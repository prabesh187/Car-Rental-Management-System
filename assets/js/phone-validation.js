/**
 * 10-Digit Phone Number Validation Script
 * Ensures all phone inputs accept only 10 digits
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all phone input fields
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name*="phone"]');
    
    phoneInputs.forEach(function(input) {
        // Add input event listener for real-time validation
        input.addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
            
            // Visual feedback
            if (this.value.length === 10) {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#f8fff9';
                this.setCustomValidity('');
            } else if (this.value.length > 0) {
                this.style.borderColor = '#dc3545';
                this.style.backgroundColor = '#fff5f5';
                this.setCustomValidity('Phone number must be exactly 10 digits');
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
                this.setCustomValidity('');
            }
        });
        
        // Add paste event listener
        input.addEventListener('paste', function(e) {
            setTimeout(() => {
                // Clean pasted content
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                this.dispatchEvent(new Event('input'));
            }, 10);
        });
        
        // Add keypress event to prevent non-numeric input
        input.addEventListener('keypress', function(e) {
            // Allow backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
            
            // Stop if already 10 digits
            if (this.value.length >= 10) {
                e.preventDefault();
            }
        });
    });
    
    // Add form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const phoneInputsInForm = form.querySelectorAll('input[type="tel"], input[name*="phone"]');
            let isValid = true;
            
            phoneInputsInForm.forEach(function(input) {
                const phone = input.value;
                
                if (input.hasAttribute('required') && (phone.length !== 10 || !/^[0-9]{10}$/.test(phone))) {
                    alert('Please enter exactly 10 digits for phone number: ' + (input.placeholder || input.name));
                    input.focus();
                    isValid = false;
                    return false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
    });
});

// Helper function to format phone number display (optional)
function formatPhoneDisplay(phoneNumber) {
    if (phoneNumber && phoneNumber.length === 10) {
        return phoneNumber.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    }
    return phoneNumber;
}

// Helper function to validate phone number
function isValidPhone(phoneNumber) {
    return /^[0-9]{10}$/.test(phoneNumber);
}