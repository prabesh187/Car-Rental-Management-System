<!DOCTYPE html>
<html>
<head>
    <title>Phone Number Validation Test</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/phone-validation.js"></script>
    <style>
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .valid { border-color: #28a745 !important; background-color: #f8fff9 !important; }
        .invalid { border-color: #dc3545 !important; background-color: #fff5f5 !important; }
    </style>
</head>
<body>

<div class="container" style="margin-top: 50px;">
    <h1><i class="fa fa-phone"></i> Phone Number Validation Test</h1>
    <p class="lead">Test the 10-digit phone number validation system</p>

    <!-- Server-side Validation Test -->
    <div class="test-section">
        <h3>1. Server-side Validation Test</h3>
        <?php
        require_once 'phone_validation.php';
        
        $test_phones = [
            '9841234567' => 'Valid 10-digit number',
            '98412345678' => 'Invalid - 11 digits',
            '984123456' => 'Invalid - 9 digits',
            '98-412-34567' => 'Valid with formatting (should be cleaned)',
            'abc9841234567' => 'Invalid - contains letters',
            '984 123 4567' => 'Valid with spaces (should be cleaned)',
            '' => 'Empty phone number',
            '0000000000' => 'Valid - all zeros'
        ];
        
        echo "<table class='table table-striped'>";
        echo "<tr><th>Phone Number</th><th>Description</th><th>Result</th><th>Cleaned</th></tr>";
        
        foreach ($test_phones as $phone => $description) {
            $error = validatePhoneWithMessage($phone, 'Phone');
            $cleaned = cleanPhone($phone);
            $isValid = empty($error);
            
            $status = $isValid ? 
                "<span class='text-success'><i class='fa fa-check'></i> Valid</span>" : 
                "<span class='text-danger'><i class='fa fa-times'></i> " . $error . "</span>";
            
            echo "<tr>";
            echo "<td><code>$phone</code></td>";
            echo "<td>$description</td>";
            echo "<td>$status</td>";
            echo "<td><code>$cleaned</code></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>

    <!-- Client-side Validation Test -->
    <div class="test-section">
        <h3>2. Client-side Validation Test</h3>
        <p>Try typing in the fields below. Only 10 digits should be allowed.</p>
        
        <form id="testForm">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Customer Phone</label>
                        <input type="tel" class="form-control" name="customer_phone" 
                               placeholder="10-digit phone number" required 
                               pattern="[0-9]{10}" maxlength="10">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Driver Phone</label>
                        <input type="tel" class="form-control" name="driver_phone" 
                               placeholder="10-digit phone number" required 
                               pattern="[0-9]{10}" maxlength="10">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Client Phone</label>
                        <input type="tel" class="form-control" name="client_phone" 
                               placeholder="10-digit phone number" required 
                               pattern="[0-9]{10}" maxlength="10">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Test Form Submission</button>
        </form>
        
        <div id="result" class="mt-3"></div>
    </div>

    <!-- Test Cases -->
    <div class="test-section">
        <h3>3. Interactive Test Cases</h3>
        <p>Click the buttons below to test different scenarios:</p>
        
        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-info btn-block" onclick="testCase('9841234567')">
                    Valid: 9841234567
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-warning btn-block" onclick="testCase('98412345678')">
                    Invalid: 98412345678 (11 digits)
                </button>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-warning btn-block" onclick="testCase('984123456')">
                    Invalid: 984123456 (9 digits)
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-info btn-block" onclick="testCase('984-123-4567')">
                    Valid: 984-123-4567 (with dashes)
                </button>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-warning btn-block" onclick="testCase('abc9841234567')">
                    Invalid: abc9841234567 (with letters)
                </button>
            </div>
            <div class="col-md-6">
                <button class="btn btn-info btn-block" onclick="testCase('984 123 4567')">
                    Valid: 984 123 4567 (with spaces)
                </button>
            </div>
        </div>
    </div>

    <!-- Implementation Status -->
    <div class="test-section">
        <h3>4. Implementation Status</h3>
        <div class="row">
            <div class="col-md-6">
                <h4><i class="fa fa-check-circle text-success"></i> Files Updated</h4>
                <ul class="list-group">
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> customersignup.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> clientsignup.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> enterdriver.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> admin_drivers.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> admin_customers.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> admin_clients.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> customer_profile.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> client_profile.php</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> driver_profile.php</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h4><i class="fa fa-cog text-info"></i> Validation Features</h4>
                <ul class="list-group">
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> HTML5 pattern validation</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> JavaScript real-time validation</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> Auto-removal of non-digits</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> 10-digit limit enforcement</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> Visual feedback (colors)</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> Form submission validation</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> Server-side validation</li>
                    <li class="list-group-item"><i class="fa fa-check text-success"></i> Phone number cleaning</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Test form submission
document.getElementById('testForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phones = {
        customer: this.customer_phone.value,
        driver: this.driver_phone.value,
        client: this.client_phone.value
    };
    
    let results = '<h4>Form Validation Results:</h4><ul>';
    let allValid = true;
    
    for (let type in phones) {
        const phone = phones[type];
        const isValid = isValidPhone(phone);
        
        results += `<li class="${isValid ? 'text-success' : 'text-danger'}">`;
        results += `<i class="fa fa-${isValid ? 'check' : 'times'}"></i> `;
        results += `${type.charAt(0).toUpperCase() + type.slice(1)} Phone: ${phone} `;
        results += `(${isValid ? 'Valid' : 'Invalid'})`;
        results += '</li>';
        
        if (!isValid) allValid = false;
    }
    
    results += '</ul>';
    
    if (allValid) {
        results += '<div class="alert alert-success"><i class="fa fa-check-circle"></i> All phone numbers are valid! Form would be submitted.</div>';
    } else {
        results += '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Some phone numbers are invalid. Please correct them.</div>';
    }
    
    document.getElementById('result').innerHTML = results;
});

// Test case function
function testCase(phoneNumber) {
    const input = document.querySelector('input[name="customer_phone"]');
    input.value = phoneNumber;
    input.dispatchEvent(new Event('input'));
    input.focus();
}
</script>

</body>
</html>