# 🚗 DRIVER NOTIFICATION SYSTEM - IMPLEMENTATION GUIDE

## **NOTIFICATION METHODS OVERVIEW**

### **1. 📧 EMAIL NOTIFICATIONS (Primary Method)**
### **2. 📱 SMS NOTIFICATIONS (Secondary Method)**
### **3. 🔔 IN-APP NOTIFICATIONS (Real-time)**
### **4. 📞 PHONE CALL NOTIFICATIONS (Urgent)**
### **5. 🌐 Web Dashboard Alerts (Always Available)**

---

## **METHOD 1: EMAIL NOTIFICATION SYSTEM**

### **Implementation Steps:**

#### **A. Create Email Notification Function**
```php
<?php
// File: driver_notifications.php

function sendDriverBookingEmail($driver_id, $booking_id) {
    require 'connection.php';
    $conn = Connect();
    
    // Get driver and booking details
    $sql = "SELECT d.driver_name, d.driver_email, d.driver_phone,
                   b.id as booking_id, b.rent_start_date, b.rent_end_date,
                   c.car_name, c.car_nameplate, cu.customer_name, cu.customer_phone,
                   b.pickup_location, b.destination
            FROM driver d
            JOIN rentedcars b ON d.driver_id = ?
            JOIN cars c ON b.car_id = c.car_id  
            JOIN customers cu ON b.customer_username = cu.customer_username
            WHERE b.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $driver_id, $booking_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $to = $result['driver_email'];
        $subject = "New Booking Assignment - Booking #" . $result['booking_id'];
        
        $message = "
        <html>
        <head>
            <title>New Booking Assignment</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .booking-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .important { color: #e74c3c; font-weight: bold; }
                .button { background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🚗 New Booking Assignment</h2>
                </div>
                <div class='content'>
                    <h3>Hello " . $result['driver_name'] . ",</h3>
                    <p>You have been assigned a new booking. Please review the details below:</p>
                    
                    <div class='booking-details'>
                        <h4>📋 Booking Information</h4>
                        <p><strong>Booking ID:</strong> #" . $result['booking_id'] . "</p>
                        <p><strong>Customer:</strong> " . $result['customer_name'] . "</p>
                        <p><strong>Customer Phone:</strong> " . $result['customer_phone'] . "</p>
                        <p><strong>Vehicle:</strong> " . $result['car_name'] . " (" . $result['car_nameplate'] . ")</p>
                        <p><strong>Start Date:</strong> " . date('M j, Y g:i A', strtotime($result['rent_start_date'])) . "</p>
                        <p><strong>End Date:</strong> " . date('M j, Y g:i A', strtotime($result['rent_end_date'])) . "</p>
                        <p><strong>Pickup Location:</strong> " . $result['pickup_location'] . "</p>
                        <p><strong>Destination:</strong> " . $result['destination'] . "</p>
                    </div>
                    
                    <div class='important'>
                        ⚠️ Please confirm your availability within 30 minutes.
                    </div>
                    
                    <p style='text-align: center; margin: 20px 0;'>
                        <a href='http://yourwebsite.com/driver_profile.php?booking_id=" . $result['booking_id'] . "' class='button'>
                            View Full Details & Confirm
                        </a>
                    </p>
                    
                    <p>If you cannot accept this booking, please contact the admin immediately.</p>
                    <p>Thank you for your service!</p>
                    
                    <hr>
                    <small>Car Rental Management System | Contact: support@yourwebsite.com</small>
                </div>
            </div>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Car Rental System <noreply@yourwebsite.com>' . "\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    return false;
}
?>
```

#### **B. Integration in Booking Process**
```php
// In your booking confirmation process (bookingconfirm.php or similar)
if ($booking_successful) {
    // Send notification to assigned driver
    $notification_sent = sendDriverBookingEmail($driver_id, $booking_id);
    
    if ($notification_sent) {
        echo "<script>alert('Booking confirmed! Driver has been notified via email.');</script>";
    } else {
        echo "<script>alert('Booking confirmed! Please contact driver manually.');</script>";
    }
}
```

---

## **METHOD 2: SMS NOTIFICATION SYSTEM**

### **Using Twilio SMS Service:**

```php
<?php
// File: sms_notifications.php

function sendDriverBookingSMS($driver_phone, $booking_id, $customer_name, $pickup_time) {
    // You'll need to sign up for Twilio and get API credentials
    $account_sid = 'your_twilio_account_sid';
    $auth_token = 'your_twilio_auth_token';
    $twilio_number = 'your_twilio_phone_number';
    
    $message = "🚗 NEW BOOKING ALERT!\n";
    $message .= "Booking ID: #$booking_id\n";
    $message .= "Customer: $customer_name\n";
    $message .= "Pickup: $pickup_time\n";
    $message .= "Please confirm ASAP!\n";
    $message .= "View details: yourwebsite.com/driver";
    
    // Twilio API call (you'll need to include Twilio SDK)
    // This is a simplified example
    $url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json";
    
    $data = array(
        'From' => $twilio_number,
        'To' => $driver_phone,
        'Body' => $message
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

// Alternative: Simple SMS using local SMS gateway
function sendSimpleSMS($driver_phone, $message) {
    // This depends on your local SMS service provider
    // Many countries have SMS gateway services
    
    $sms_gateway_url = "http://your-sms-gateway.com/send";
    $data = array(
        'phone' => $driver_phone,
        'message' => $message,
        'api_key' => 'your_api_key'
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sms_gateway_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
?>
```

---

## **METHOD 3: IN-APP NOTIFICATION SYSTEM**

### **A. Create Notifications Table**
```sql
CREATE TABLE driver_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    booking_id INT NOT NULL,
    notification_type ENUM('booking_assigned', 'booking_cancelled', 'booking_modified') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES driver(driver_id),
    FOREIGN KEY (booking_id) REFERENCES rentedcars(id)
);
```

### **B. Notification Functions**
```php
<?php
// File: in_app_notifications.php

function createDriverNotification($driver_id, $booking_id, $type, $title, $message) {
    require 'connection.php';
    $conn = Connect();
    
    $sql = "INSERT INTO driver_notifications (driver_id, booking_id, notification_type, title, message) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $driver_id, $booking_id, $type, $title, $message);
    
    return $stmt->execute();
}

function getDriverNotifications($driver_id, $unread_only = false) {
    require 'connection.php';
    $conn = Connect();
    
    $where_clause = $unread_only ? "AND is_read = FALSE" : "";
    
    $sql = "SELECT * FROM driver_notifications 
            WHERE driver_id = ? $where_clause 
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function markNotificationAsRead($notification_id) {
    require 'connection.php';
    $conn = Connect();
    
    $sql = "UPDATE driver_notifications SET is_read = TRUE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notification_id);
    
    return $stmt->execute();
}
?>
```

### **C. Driver Dashboard Integration**
```php
// In driver_profile.php or driver dashboard
<?php
session_start();
require 'in_app_notifications.php';

if (isset($_SESSION['login_driver'])) {
    $driver_id = $_SESSION['driver_id'];
    $notifications = getDriverNotifications($driver_id, true); // Get unread notifications
    $notification_count = count($notifications);
}
?>

<!-- Add to driver dashboard HTML -->
<div class="notification-bell" onclick="showNotifications()">
    <i class="fa fa-bell"></i>
    <?php if ($notification_count > 0): ?>
    <span class="notification-badge"><?php echo $notification_count; ?></span>
    <?php endif; ?>
</div>

<div id="notificationPanel" class="notification-panel" style="display: none;">
    <h4>Notifications</h4>
    <?php foreach ($notifications as $notification): ?>
    <div class="notification-item" data-id="<?php echo $notification['id']; ?>">
        <h5><?php echo $notification['title']; ?></h5>
        <p><?php echo $notification['message']; ?></p>
        <small><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
    </div>
    <?php endforeach; ?>
</div>

<script>
function showNotifications() {
    document.getElementById('notificationPanel').style.display = 'block';
    // Mark notifications as read via AJAX
    markNotificationsAsRead();
}

function markNotificationsAsRead() {
    // AJAX call to mark notifications as read
    $.ajax({
        url: 'mark_notifications_read.php',
        method: 'POST',
        data: { driver_id: <?php echo $driver_id; ?> },
        success: function(response) {
            $('.notification-badge').hide();
        }
    });
}
</script>
```

---

## **METHOD 4: PHONE CALL NOTIFICATIONS**

### **Using Twilio Voice API:**
```php
<?php
function callDriverForUrgentBooking($driver_phone, $booking_id) {
    $account_sid = 'your_twilio_account_sid';
    $auth_token = 'your_twilio_auth_token';
    $twilio_number = 'your_twilio_phone_number';
    
    $message = "Hello, you have received an urgent booking assignment. ";
    $message .= "Booking ID is $booking_id. ";
    $message .= "Please check your email or login to the system immediately. ";
    $message .= "Thank you.";
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Calls.json";
    
    $data = array(
        'From' => $twilio_number,
        'To' => $driver_phone,
        'Twiml' => '<Response><Say>' . $message . '</Say></Response>'
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
?>
```

---

## **METHOD 5: COMPREHENSIVE NOTIFICATION SYSTEM**

### **Master Notification Function:**
```php
<?php
// File: master_notification_system.php

function notifyDriverAboutBooking($driver_id, $booking_id, $urgency = 'normal') {
    require 'connection.php';
    $conn = Connect();
    
    // Get driver and booking details
    $sql = "SELECT d.*, b.*, c.car_name, cu.customer_name 
            FROM driver d
            JOIN rentedcars b ON b.driver_id = d.driver_id
            JOIN cars c ON b.car_id = c.car_id
            JOIN customers cu ON b.customer_username = cu.customer_username
            WHERE d.driver_id = ? AND b.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $driver_id, $booking_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if (!$data) return false;
    
    $results = array();
    
    // 1. Always create in-app notification
    $title = "New Booking Assignment #" . $booking_id;
    $message = "You have been assigned to drive " . $data['customer_name'] . 
               " in " . $data['car_name'] . " on " . 
               date('M j, Y', strtotime($data['rent_start_date']));
    
    $results['in_app'] = createDriverNotification($driver_id, $booking_id, 'booking_assigned', $title, $message);
    
    // 2. Send email notification
    $results['email'] = sendDriverBookingEmail($driver_id, $booking_id);
    
    // 3. Send SMS for urgent bookings or if email fails
    if ($urgency == 'urgent' || !$results['email']) {
        $sms_message = "🚗 URGENT: New booking #$booking_id assigned. Customer: " . $data['customer_name'] . 
                      ". Check your email/dashboard immediately!";
        $results['sms'] = sendSimpleSMS($data['driver_phone'], $sms_message);
    }
    
    // 4. Phone call for very urgent bookings
    if ($urgency == 'very_urgent') {
        $results['call'] = callDriverForUrgentBooking($data['driver_phone'], $booking_id);
    }
    
    // Log notification attempts
    logNotificationAttempt($driver_id, $booking_id, $results);
    
    return $results;
}

function logNotificationAttempt($driver_id, $booking_id, $results) {
    require 'connection.php';
    $conn = Connect();
    
    $log_data = json_encode($results);
    $sql = "INSERT INTO notification_logs (driver_id, booking_id, notification_methods, sent_at) 
            VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $driver_id, $booking_id, $log_data);
    $stmt->execute();
}
?>
```

---

## **IMPLEMENTATION IN BOOKING PROCESS**

### **Integration Example:**
```php
// In your booking confirmation process
if ($booking_successful) {
    // Determine urgency based on pickup time
    $pickup_time = strtotime($rent_start_date);
    $current_time = time();
    $time_difference = $pickup_time - $current_time;
    
    if ($time_difference < 3600) { // Less than 1 hour
        $urgency = 'very_urgent';
    } elseif ($time_difference < 7200) { // Less than 2 hours
        $urgency = 'urgent';
    } else {
        $urgency = 'normal';
    }
    
    // Send notifications
    $notification_results = notifyDriverAboutBooking($driver_id, $booking_id, $urgency);
    
    // Provide feedback to customer
    if ($notification_results['email'] || $notification_results['sms']) {
        echo "<script>alert('Booking confirmed! Driver has been notified and will contact you shortly.');</script>";
    } else {
        echo "<script>alert('Booking confirmed! We will contact the driver manually.');</script>";
    }
}
```

---

## **RECOMMENDED IMPLEMENTATION STRATEGY**

### **Phase 1: Basic Implementation**
1. ✅ **Email Notifications** (Primary method)
2. ✅ **In-App Notifications** (Dashboard alerts)
3. ✅ **Simple SMS** (Backup method)

### **Phase 2: Advanced Features**
4. ✅ **Phone Call Notifications** (Urgent bookings)
5. ✅ **Push Notifications** (Mobile app)
6. ✅ **WhatsApp Integration** (Popular messaging)

### **Phase 3: Smart Features**
7. ✅ **AI-powered Timing** (Best time to notify)
8. ✅ **Multi-language Support** (Driver preferences)
9. ✅ **Delivery Confirmation** (Read receipts)

This comprehensive notification system ensures drivers are always informed about new bookings through multiple channels, improving response times and customer satisfaction!