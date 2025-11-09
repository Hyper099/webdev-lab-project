<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

try {
   if (!isset($_SESSION['user_id'])) {
      echo json_encode(['success' => false, 'error' => 'Unauthorized']);
      exit();
   }

   $user_id = $_SESSION['user_id'];

   $request_type = isset($_GET['type']) ? $_GET['type'] : 'analysis';

// Fetch user expenses
$stmt = $conn->prepare("SELECT category, amount, date, description FROM expenses WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($expenses)) {
   echo json_encode([
      'success' => true,
      'response' => "You haven't recorded any expenses yet. Start tracking your spending to get personalized AI insights!"
   ]);
   exit();
}

// Calculate statistics
$total_amount = 0;
$category_totals = [];
$recent_expenses = [];

foreach ($expenses as $expense) {
   $total_amount += $expense['amount'];

   if (!isset($category_totals[$expense['category']])) {
      $category_totals[$expense['category']] = 0;
   }
   $category_totals[$expense['category']] += $expense['amount'];

   // Get expenses from last 30 days
   $expense_date = strtotime($expense['date']);
   if ($expense_date >= strtotime('-30 days')) {
      $recent_expenses[] = $expense;
   }
}

arsort($category_totals);

// Prepare expense summary for AI
$expense_summary = "Total Expenses: ₹" . number_format($total_amount, 2) . "\n";
$expense_summary .= "Number of Transactions: " . count($expenses) . "\n\n";
$expense_summary .= "Category Breakdown:\n";

foreach ($category_totals as $category => $amount) {
   $percentage = ($amount / $total_amount) * 100;
   $expense_summary .= "- $category: ₹" . number_format($amount, 2) . " (" . number_format($percentage, 1) . "%)\n";
}

$expense_summary .= "\nRecent Expenses (Last 30 days): " . count($recent_expenses) . " transactions\n";

// Create AI prompt based on request type
   if ($request_type === 'advice') {
      $prompt = "As a financial advisor, analyze this expense data and provide 3-4 actionable money-saving tips. Be concise (max 300 words).\n\n" . $expense_summary;
   } else {
      $prompt = "As a financial analyst, provide a brief analysis of these expenses with key insights and 2-3 recommendations. Be concise (max 300 words).\n\n" . $expense_summary;
   }

   // For now, just use mock response since API has issues
   // Build the expense summary for mock response
   $total_amount = 0;
   $category_totals = [];
   
   foreach ($expenses as $expense) {
      $total_amount += $expense['amount'];
      if (!isset($category_totals[$expense['category']])) {
         $category_totals[$expense['category']] = 0;
      }
      $category_totals[$expense['category']] += $expense['amount'];
   }
   
   arsort($category_totals);
   
   $expense_summary = "Total Expenses: ₹" . number_format($total_amount, 2) . "\n";
   $expense_summary .= "Number of Transactions: " . count($expenses) . "\n\n";
   $expense_summary .= "Category Breakdown:\n";
   
   foreach ($category_totals as $category => $amount) {
      $percentage = ($amount / $total_amount) * 100;
      $expense_summary .= "- $category: ₹" . number_format($amount, 2) . " (" . number_format($percentage, 1) . "%)\n";
   }
   
   if ($request_type === 'advice') {
      $prompt = "As a financial advisor, analyze this expense data and provide 3-4 actionable money-saving tips.\n\n" . $expense_summary;
   } else {
      $prompt = "As a financial analyst, provide a brief analysis of these expenses with key insights and recommendations.\n\n" . $expense_summary;
   }
   
   $ai_response = getMockAIResponse($prompt);

   echo json_encode($ai_response);

} catch (Exception $e) {
   echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

function callGeminiAPI($prompt, $expenses = [], $request_type = 'general')
{
   $api_key = GEMINI_API_KEY;

   // Check if API key is configured
   if ($api_key === 'YOUR_GOOGLE_GEMINI_API_KEY_HERE') {
      // Return mock response if API key not configured
      return getMockAIResponse($prompt);
   }

   $url = GEMINI_API_URL . '?key=' . $api_key;

   $data = [
      'contents' => [
         [
            'parts' => [
               ['text' => $prompt]
            ]
         ]
      ],
      'generationConfig' => [
         'temperature' => 0.7,
         'maxOutputTokens' => 500,
         'topP' => 0.95,
         'topK' => 40
      ]
   ];

   $ch = curl_init($url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json'
   ]);
   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
   curl_setopt($ch, CURLOPT_TIMEOUT, 30);

   $response = curl_exec($ch);
   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

   if (curl_errno($ch)) {
      $error = curl_error($ch);
      curl_close($ch);
      // Return mock response on curl error
      return getMockAIResponse($expenses, $request_type);
   }

   curl_close($ch);

   if ($http_code !== 200) {
      // Decode response to get detailed error
      $error_response = json_decode($response, true);
      $error_message = 'API returned error: ' . $http_code;
      
      if (isset($error_response['error']['message'])) {
         $error_message .= ' - ' . $error_response['error']['message'];
      }
      
      return ['success' => false, 'error' => $error_message, 'debug' => $response];
   }

   $result = json_decode($response, true);

   // Check if response was incomplete due to token limit - use mock response instead
   if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'MAX_TOKENS') {
      return getMockAIResponse($expenses, $request_type);
   }

   // Check for various possible response structures
   if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
      $ai_text = $result['candidates'][0]['content']['parts'][0]['text'];
      return ['success' => true, 'response' => $ai_text];
   }
   
   // Alternative structure
   if (isset($result['candidates'][0]['output'])) {
      $ai_text = $result['candidates'][0]['output'];
      return ['success' => true, 'response' => $ai_text];
   }
   
   // Another possible structure
   if (isset($result['text'])) {
      return ['success' => true, 'response' => $result['text']];
   }
   
   // Check if content exists but parts is missing
   if (isset($result['candidates'][0]['content'])) {
      return ['success' => false, 'error' => 'Response format error - content exists but no text found', 'debug' => json_encode($result, JSON_PRETTY_PRINT)];
   }

   // If we got here, log the actual response structure for debugging
   return ['success' => false, 'error' => 'Invalid API response format', 'debug' => json_encode($result, JSON_PRETTY_PRINT)];
}

/**
 * Mock AI Response (Used when API key is not configured)
 */
function getMockAIResponse($prompt)
{
   // Extract key information from prompt
   preg_match('/Total Expenses: ₹([\d,\.]+)/', $prompt, $total_match);
   preg_match_all('/- (\w+): ₹([\d,\.]+) \(([\d\.]+)%\)/', $prompt, $category_matches, PREG_SET_ORDER);

   $total = isset($total_match[1]) ? str_replace(',', '', $total_match[1]) : 0;

   // Find highest spending category
   $max_category = '';
   $max_percentage = 0;

   foreach ($category_matches as $match) {
      $percentage = floatval($match[3]);
      if ($percentage > $max_percentage) {
         $max_percentage = $percentage;
         $max_category = $match[1];
      }
   }

   // Generate intelligent response based on data
   $responses = [];

   // Analysis based on total spending
   if ($total > 10000) {
      $responses[] = "Your total spending of ₹" . number_format($total, 2) . " is on the higher side. Let's look at optimization opportunities.";
   } else {
      $responses[] = "Your total spending of ₹" . number_format($total, 2) . " shows moderate financial activity.";
   }

   // Category-specific insights
   if (!empty($max_category)) {
      $responses[] = "\n\n📊 Key Insight: Your highest spending category is $max_category at " . number_format($max_percentage, 1) . "% of total expenses.";

      if ($max_percentage > 40) {
         $responses[] = "\n\n⚠️ Alert: $max_category consumes a significant portion of your budget. Consider reviewing these expenses.";

         switch ($max_category) {
            case 'Food':
               $responses[] = "\n💡 Saving Tips:\n- Plan meals in advance to reduce impulse dining\n- Cook at home more often\n- Limit restaurant visits to 2-3 times per week\n- Potential savings: ₹" . number_format($total * 0.15, 0) . " per month (15% reduction)";
               break;
            case 'Shopping':
               $responses[] = "\n💡 Saving Tips:\n- Create a shopping list before purchases\n- Wait 24 hours before buying non-essentials\n- Look for discounts and compare prices\n- Potential savings: ₹" . number_format($total * 0.20, 0) . " per month (20% reduction)";
               break;
            case 'Entertainment':
               $responses[] = "\n💡 Saving Tips:\n- Explore free entertainment options\n- Set a monthly entertainment budget\n- Share subscription costs with family\n- Potential savings: ₹" . number_format($total * 0.25, 0) . " per month (25% reduction)";
               break;
            default:
               $responses[] = "\n💡 Recommendation: Review $max_category expenses and identify areas to cut back by 15-20%.";
         }
      }
   }

   // Diversification insight
   $category_count = count($category_matches);
   if ($category_count <= 2) {
      $responses[] = "\n\n📝 Note: You're tracking only $category_count categories. Consider breaking down expenses further for better insights.";
   }

   // General recommendations
   $responses[] = "\n\n✅ Recommendations:\n";
   $responses[] = "1. Set a monthly budget limit of ₹" . number_format($total * 0.9, 0) . " (10% reduction goal)\n";
   $responses[] = "2. Track all expenses daily for better awareness\n";
   $responses[] = "3. Build an emergency fund covering 3-6 months of expenses\n";
   $responses[] = "4. Review and optimize expenses weekly";

   return [
      'success' => true,
      'response' => implode('', $responses),
      'mock' => true
   ];
}
