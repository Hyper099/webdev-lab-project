<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = '';

// Add expense
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    
    if (empty($category) || $amount <= 0 || empty($date)) {
        $message = '<div class="message error">Please fill in all required fields correctly</div>';
    } else {
        $query = "INSERT INTO expenses (user_id, category, amount, date, description) VALUES ($user_id, '$category', $amount, '$date', '$description')";
        
        if (mysqli_query($conn, $query)) {
            $message = '<div class="message success">Expense added successfully!</div>';
        } else {
            $message = '<div class="message error">Failed to add expense</div>';
        }
    }
}

// Delete expense
if (isset($_GET['delete'])) {
    $expense_id = $_GET['delete'];
    $delete_query = "DELETE FROM expenses WHERE id = $expense_id AND user_id = $user_id";
    mysqli_query($conn, $delete_query);
    header('Location: dashboard.php');
    exit();
}

// Get all expenses
$expenses_query = "SELECT id, category, amount, date, description FROM expenses WHERE user_id = $user_id ORDER BY date DESC";
$expenses_result = mysqli_query($conn, $expenses_query);
$expenses = [];

if ($expenses_result) {
    while ($row = mysqli_fetch_assoc($expenses_result)) {
        $expenses[] = $row;
    }
}

// Calculate totals
$total_expenses = 0;
$category_totals = [];

foreach ($expenses as $expense) {
    $total_expenses += $expense['amount'];
    
    if (!isset($category_totals[$expense['category']])) {
        $category_totals[$expense['category']] = 0;
    }
    $category_totals[$expense['category']] += $expense['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Expense Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/charts.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="css/button.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>💰 Expense Tracker</h1>
            <nav>
                <span style="color: #ffffff; margin-right: 15px;">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>
    
    <div class="container dashboard">
        <?php echo $message; ?>
        
        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <h3>Total Expenses</h3>
                <div class="value">₹<?php echo number_format($total_expenses, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Entries</h3>
                <div class="value"><?php echo count($expenses); ?></div>
            </div>
            <div class="stat-card">
                <h3>Categories</h3>
                <div class="value"><?php echo count($category_totals); ?></div>
            </div>
        </div>
        
        <!-- Add Expense Button -->
        <div class="add-expense-section">
            <button id="addExpenseBtn" class="btn-add-expense">
                <span class="btn-icon">+</span>
                <span>Add New Expense</span>
            </button>
        </div>
        
        <div class="dashboard-grid">
            <!-- Category Breakdown with Charts -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Category Breakdown</h2>
                    <?php if (!empty($category_totals)): ?>
                        <div class="chart-toggle">
                            <button class="chart-btn active" data-chart="bar" title="Bar Chart">📊</button>
                            <button class="chart-btn" data-chart="pie" title="Pie Chart">🥧</button>
                            <button class="chart-btn" data-chart="table" title="Table View">📋</button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($category_totals)): ?>
                    <div class="empty-state">
                        <p>No expenses recorded yet</p>
                    </div>
                <?php else: ?>
                    <?php 
                    arsort($category_totals);
                    $highest_category = key($category_totals);
                    $highest_amount = $category_totals[$highest_category];
                    end($category_totals);
                    $lowest_category = key($category_totals);
                    $lowest_amount = $category_totals[$lowest_category];
                    $avg_per_category = $total_expenses / count($category_totals);
                    ?>
                    
                    <div class="category-stats">
                        <div class="mini-stat">
                            <span class="stat-label">Highest</span>
                            <span class="stat-value"><?php echo $highest_category; ?> (₹<?php echo number_format($highest_amount, 0); ?>)</span>
                        </div>
                        <div class="mini-stat">
                            <span class="stat-label">Average</span>
                            <span class="stat-value">₹<?php echo number_format($avg_per_category, 0); ?></span>
                        </div>
                        <div class="mini-stat">
                            <span class="stat-label">Lowest</span>
                            <span class="stat-value"><?php echo $lowest_category; ?> (₹<?php echo number_format($lowest_amount, 0); ?>)</span>
                        </div>
                    </div>
                    
                    <div id="barChart" class="chart-view active">
                        <?php foreach ($category_totals as $category => $total): ?>
                            <?php $percentage = ($total / $total_expenses) * 100; ?>
                            <div class="bar-item">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <strong><?php echo $category; ?></strong>
                                    <span>₹<?php echo number_format($total, 2); ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                                </div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: <?php echo $percentage; ?>%;">
                                        <span class="bar-label"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div id="pieChart" class="chart-view">
                        <canvas id="pieCanvas" width="400" height="400"></canvas>
                        <div id="pieLegend" class="pie-legend"></div>
                    </div>
                    
                    <div id="tableChart" class="chart-view">
                        <table class="category-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Percentage</th>
                                    <th>vs Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($category_totals as $category => $total): 
                                    $percentage = ($total / $total_expenses) * 100;
                                    $vs_avg = (($total - $avg_per_category) / $avg_per_category) * 100;
                                ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><strong><?php echo $category; ?></strong></td>
                                        <td>₹<?php echo number_format($total, 2); ?></td>
                                        <td><?php echo number_format($percentage, 1); ?>%</td>
                                        <td class="<?php echo $vs_avg >= 0 ? 'above-avg' : 'below-avg'; ?>">
                                            <?php echo ($vs_avg >= 0 ? '+' : '') . number_format($vs_avg, 0); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <script>
                        const categoryData = <?php echo json_encode(array_values(array_map(function($cat, $amt) {
                            return ['category' => $cat, 'amount' => $amt];
                        }, array_keys($category_totals), $category_totals))); ?>;
                    </script>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h2>🤖 AI Analysis & Insights</h2>
                <div class="ai-trigger">
                    <button id="analyzeBtn" class="btn">Analyze My Spending</button>
                    <button id="adviceBtn" class="btn btn-secondary">Get Saving Advice</button>
                </div>
                <div id="aiResponse" class="ai-response">
                    <div class="empty-state">
                        <p>Click "Analyze My Spending" to get AI-powered insights about your expenses and saving recommendations.</p>
                    </div>
                </div>
            </div>
            
            <div class="card expense-list">
                <h2>Recent Expenses</h2>
                <?php if (empty($expenses)): ?>
                    <div class="empty-state">
                        <p>No expenses recorded yet. Start by adding your first expense!</p>
                    </div>
                <?php else: ?>
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo isset($expense['date']) ? date('M d, Y', strtotime($expense['date'])) : '-'; ?></td>
                                    <td><?php echo isset($expense['category']) ? $expense['category'] : '-'; ?></td>
                                    <td class="amount">₹<?php echo isset($expense['amount']) ? number_format($expense['amount'], 2) : '0.00'; ?></td>
                                    <td><?php echo isset($expense['description']) && $expense['description'] ? $expense['description'] : '-'; ?></td>
                                    <td>
                                        <button onclick="deleteExpense(<?php echo isset($expense['id']) ? $expense['id'] : 0; ?>)" class="delete-btn">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div id="expenseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Expense</h2>
                <span class="close">&times;</span>
            </div>
            <form method="POST" action="" class="expense-form">
                <div class="form-group">
                    <label for="modal_category">Category *</label>
                    <select id="modal_category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Food">Food</option>
                        <option value="Travel">Travel</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Bills">Bills</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Education">Education</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="modal_amount">Amount (₹) *</label>
                    <input type="number" id="modal_amount" name="amount" step="0.01" min="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="modal_date">Date *</label>
                    <input type="date" id="modal_date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="modal_description">Description</label>
                    <textarea id="modal_description" name="description" placeholder="Optional notes..."></textarea>
                </div>
                
                <button type="submit" name="add_expense" class="btn btn-full">Add Expense</button>
            </form>
        </div>
    </div>
    
    <script src="js/common.js"></script>
    <script src="js/charts.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/modal.js"></script>
</body>
</html>
