<?php
/**
 * Server-side Phone Number Validation Functions
 * Ensures all phone numbers are exactly 10 digits
 */

/**
 * Validate phone number - must be exactly 10 digits
 * @param string $phone The phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if exactly 10 digits
    return strlen($phone) === 10 && ctype_digit($phone);
}

/**
 * Clean phone number - remove non-digits and limit to 10 digits
 * @param string $phone The phone number to clean
 * @return string Cleaned phone number
 */
function cleanPhone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Limit to 10 digits
    return substr($phone, 0, 10);
}

/**
 * Format phone number for display (optional)
 * @param string $phone The phone number to format
 * @return string Formatted phone number (XXX-XXX-XXXX)
 */
function formatPhoneDisplay($phone) {
    $phone = cleanPhone($phone);
    
    if (strlen($phone) === 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
    }
    
    return $phone;
}

/**
 * Validate and return error message if invalid
 * @param string $phone The phone number to validate
 * @param string $fieldName The name of the field for error message
 * @return string Empty string if valid, error message if invalid
 */
function validatePhoneWithMessage($phone, $fieldName = 'Phone number') {
    if (empty($phone)) {
        return "$fieldName is required.";
    }
    
    $cleanedPhone = cleanPhone($phone);
    
    if (strlen($cleanedPhone) !== 10) {
        return "$fieldName must be exactly 10 digits.";
    }
    
    if (!ctype_digit($cleanedPhone)) {
        return "$fieldName must contain only numbers.";
    }
    
    return ''; // Valid
}

/**
 * Example usage in forms:
 * 
 * // In your form processing PHP file:
 * require_once 'phone_validation.php';
 * 
 * if ($_POST) {
 *     $phone = $_POST['phone'];
 *     $error = validatePhoneWithMessage($phone, 'Phone Number');
 *     
 *     if ($error) {
 *         // Display error
 *         echo $error;
 *     } else {
 *         // Clean and save
 *         $cleanPhone = cleanPhone($phone);
 *         // Save $cleanPhone to database
 *     }
 * }
 */
?>