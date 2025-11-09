<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
   echo json_encode(['success' => false, 'error' => 'Unauthorized']);
   exit();
}

$user_id = $_SESSION['user_id'];
$request_type = isset($_GET['type']) ? $_GET['type'] : 'analysis';

// Get user expenses
$query = "SELECT category, amount, date, description FROM expenses WHERE user_id = $user_id ORDER BY date DESC";
$result = mysqli_query($conn, $query);
$expenses = [];

while ($row = mysqli_fetch_assoc($result)) {
   $expenses[] = $row;
}

if (empty($expenses)) {
   echo json_encode([
      'success' => true,
      'response' => "You haven't recorded any expenses yet. Start tracking your spending to get personalized AI insights!"
   ]);
   exit();
}

// Calculate totals
$total_amount = 0;
$category_totals = [];
$recent_expenses = [];

foreach ($expenses as $expense) {
   $total_amount += $expense['amount'];
   
   if (!isset($category_totals[$expense['category']])) {
      $category_totals[$expense['category']] = 0;
   }
   $category_totals[$expense['category']] += $expense['amount'];
   
   $expense_date = strtotime($expense['date']);
   if ($expense_date >= strtotime('-30 days')) {
      $recent_expenses[] = $expense;
   }
}

arsort($category_totals);

// Prepare summary
$expense_summary = "Total Expenses: ₹" . number_format($total_amount, 2) . "\n";
$expense_summary .= "Number of Transactions: " . count($expenses) . "\n\n";
$expense_summary .= "Category Breakdown:\n";

foreach ($category_totals as $category => $amount) {
   $percentage = ($amount / $total_amount) * 100;
   $expense_summary .= "- $category: ₹" . number_format($amount, 2) . " (" . number_format($percentage, 1) . "%)\n";
}

$expense_summary .= "\nRecent Expenses (Last 30 days): " . count($recent_expenses) . " transactions\n";

// Create prompt
if ($request_type === 'advice') {
   $prompt = "You are a personal finance advisor. Based on the following expense data, provide detailed, actionable financial advice:\n\n" . $expense_summary . "\n\nProvide:\n1. A comprehensive overview of the spending pattern (2-3 sentences)\n2. 4-5 specific, actionable money-saving tips with explanations of how they will help\n3. Budget recommendations with specific amounts or percentages\n4. Long-term financial planning suggestions\n\nUse emojis to make it engaging. Be specific, descriptive, and practical. Format the response in a clear, easy-to-read structure.";
} else {
   $prompt = "You are an expert financial analyst. Analyze the following expense data in detail:\n\n" . $expense_summary . "\n\nProvide a comprehensive analysis including:\n1. **Key Insights**: Detailed observation about spending patterns, trends, and what they reveal (2-3 sentences)\n2. **Category Deep-Dive**: Analyze the top 2-3 spending categories with specific observations and concerns\n3. **Optimization Strategies**: Provide 4-5 detailed, actionable recommendations with explanations of potential savings\n4. **Financial Health Assessment**: Evaluate the overall financial situation and provide specific guidance\n5. **Risk & Opportunities**: Highlight potential financial risks and opportunities for improvement\n\nUse emojis and formatting to make it engaging and easy to read. Be specific with numbers and percentages where relevant.";
}

// Call AI
$ai_response = getAIResponse($prompt);
echo json_encode($ai_response);

function getAIResponse($prompt) {
   $api_key = GEMINI_API_KEY;
   
   if (!$api_key || $api_key === 'YOUR_GOOGLE_GEMINI_API_KEY_HERE') {
      return ['success' => false, 'error' => 'Gemini API key is not configured. Please add your API key in config.php'];
   }
   
   $url = GEMINI_API_URL . '?key=' . $api_key;
   
   $data = [
      'contents' => [
         [ 'role' => 'user', 'parts' => [ ['text' => $prompt] ] ]
      ],
      'generationConfig' => [
         'temperature' => 0.7,
         'maxOutputTokens' => 800,
         'topP' => 0.95,
         'topK' => 40
      ]
   ];
   
   $ch = curl_init($url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
   curl_setopt($ch, CURLOPT_TIMEOUT, 30);
   
   $response = curl_exec($ch);
   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   $curl_error = curl_error($ch);
   $curl_errno = curl_errno($ch);
   curl_close($ch);
   
   if ($curl_errno) {
      return ['success' => false, 'error' => 'CURL Error: ' . $curl_error];
   }
   
   if ($http_code !== 200) {
      $error_response = json_decode($response, true);
      $error_message = 'Gemini API Error (HTTP ' . $http_code . ')';
      
      if (isset($error_response['error']['message'])) {
         $error_message .= ': ' . $error_response['error']['message'];
      }
      
      return ['success' => false, 'error' => $error_message, 'debug' => $response];
   }
   
   $result = json_decode($response, true);
   
   if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
      $ai_text = $result['candidates'][0]['content']['parts'][0]['text'];
      return ['success' => true, 'response' => $ai_text];
   }
   
   if (isset($result['candidates'][0]['finishReason'])) {
      $finishReason = $result['candidates'][0]['finishReason'];
      if ($finishReason !== 'STOP') {
         return ['success' => false, 'error' => 'Response incomplete. Finish reason: ' . $finishReason, 'debug' => json_encode($result, JSON_PRETTY_PRINT)];
      }
   }
   
   return ['success' => false, 'error' => 'Unexpected API response format', 'debug' => json_encode($result, JSON_PRETTY_PRINT)];
}
?>