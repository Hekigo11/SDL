// Date Selection Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Step 1 Functionality
    const dateTypeInputs = document.querySelectorAll('input[name="event_date_type"]');
    const nextAvailableContainer = document.getElementById('next_available_container');
    const specificDateContainer = document.getElementById('specific_date_container');
    const numPersonsInput = document.getElementById('num_persons');
    const packageRadios = document.querySelectorAll('.package-select');

    if (dateTypeInputs.length) {
        dateTypeInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value === 'next_available') {
                    nextAvailableContainer.style.display = 'block';
                    specificDateContainer.style.display = 'none';
                } else {
                    nextAvailableContainer.style.display = 'none';
                    specificDateContainer.style.display = 'block';
                }
            });
        });
    }

    // Update date restrictions based on group size
    if (numPersonsInput) {
        numPersonsInput.addEventListener('change', function() {
            updateDateRestrictions();
            showSmallGroupWarning();
            calculateTotal();
        });
    }

    // Package selection handlers
    if (packageRadios.length) {
        packageRadios.forEach(radio => {
            radio.addEventListener('change', calculateTotal);
        });
    }

    // Step 2 Functionality
    const serviceCheckboxes = document.querySelectorAll('input[name="options[]"]');
    if (serviceCheckboxes.length) {
        serviceCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', calculateTotal);
        });
    }

    // Form submission handler for special cases
    const form = document.getElementById('cateringForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (checkForSpecialCases()) {
                e.preventDefault(); // Stop form submission
                
                // Set appropriate message
                const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
                const customPackage = document.querySelector('#package_custom:checked');
                let message = '';
                
                if (customPackage && numPersons < 50) {
                    message = 'You have selected a custom package and specified fewer than 50 persons. For these special requests, our staff will need to contact you directly.';
                } else if (customPackage) {
                    message = 'You have selected a custom package. Our staff will contact you to discuss your requirements and provide a quote.';
                } else if (numPersons < 50) {
                    message = 'You have requested catering for fewer than 50 persons. For smaller groups, our staff will need to contact you to discuss options.';
                }
                
                document.getElementById('customRequestMessage').textContent = message;
                $('#customRequestModal').modal('show');
            }
        });
    }

    // Handle the checkbox in the modal
    const proceedAnywayCheck = document.getElementById('proceedAnywayCheck');
    const proceedAnywayBtn = document.getElementById('proceedAnywayBtn');
    
    if (proceedAnywayCheck && proceedAnywayBtn) {
        proceedAnywayCheck.addEventListener('change', function() {
            proceedAnywayBtn.disabled = !this.checked;
        });

        proceedAnywayBtn.addEventListener('click', function() {
            $('#customRequestModal').modal('hide');
            document.getElementById('cateringForm').submit();
        });
    }

    // Initialize calculations if we're on step 2
    const urlPath = window.location.pathname;
    if (urlPath.endsWith('step2.php')) {
        initializeStep2Costs();
    }

    // Initialize
    if (numPersonsInput) {
        updateDateRestrictions();
        calculateTotal();
        
        // Check if we need to show the small group warning on page load
        const initialNumPersons = parseInt(numPersonsInput.value) || 0;
        if (initialNumPersons < 50) {
            document.getElementById('smallGroupWarning').style.display = 'block';
        }
    }
});

function updateDateRestrictions() {
    const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
    const dateInput = document.getElementById('event_date');
    const helperText = document.getElementById('dateHelperText');
    
    if (!dateInput || !helperText) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const minDays = numPersons >= 100 ? 15 : 4;
    const minDate = new Date(today);
    minDate.setDate(today.getDate() + minDays);
    
    const minDateStr = minDate.toISOString().split('T')[0];
    dateInput.min = minDateStr;
    
    const maxDate = new Date(today);
    maxDate.setMonth(today.getMonth() + 3);
    const maxDateStr = maxDate.toISOString().split('T')[0];
    dateInput.max = maxDateStr;
    
    const advanceDays = minDays - 1;
    helperText.textContent = `Please book at least ${advanceDays} days in advance${numPersons >= 100 ? ' for large groups (100+ persons)' : ''}.`;
    
    if (dateInput.value) {
        const selectedDate = new Date(dateInput.value);
        selectedDate.setHours(0, 0, 0, 0);
        if (selectedDate < minDate) {
            dateInput.value = '';
            showAlert(`Please book at least ${advanceDays} days in advance. Earliest available date is ${minDate.toLocaleDateString()}.`, 'warning');
        }
    }
    
    updateQuickDateOptions(minDate);
}

function updateQuickDateOptions(minDate) {
    const container = document.querySelector('#next_available_container .row');
    if (!container) return;

    container.innerHTML = '';
    
    for (let i = 0; i < 5; i++) {
        const date = new Date(minDate);
        date.setDate(minDate.getDate() + i);
        
        const dateStr = date.toISOString().split('T')[0];
        const formattedDate = date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const div = document.createElement('div');
        div.className = 'col-md-6 mb-2';
        div.innerHTML = `
            <div class="custom-control custom-radio">
                <input type="radio" id="quick_date_${i}" 
                       name="quick_date" 
                       value="${dateStr}" 
                       class="custom-control-input"
                       ${i === 0 ? 'checked' : ''}>
                <label class="custom-control-label" for="quick_date_${i}">
                    <strong>${formattedDate}</strong>
                </label>
            </div>
        `;
        
        container.appendChild(div);
    }
}

function showSmallGroupWarning() {
    const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
    const warning = document.getElementById('smallGroupWarning');
    
    if (warning) {
        warning.style.display = numPersons < 50 ? 'block' : 'none';
    }
}

function calculateTotal() {
    console.log('Calculate total called');
    
    const numPersons = parseInt(document.querySelector('input[name="num_persons"]').value) || 0;
    console.log('Number of persons:', numPersons);
    
    let packageCost = 0;
    let servicesCost = 0;    // First try to get price from package_price field
    let packagePrice = 0;
    const packagePriceInput = document.querySelector('input[name="package_price"]');
    // Try to get either a checked radio button or a hidden field for step 2
    const packageInput = document.querySelector('input[name="menu_bundle"]:checked') || 
                        document.querySelector('input[name="menu_bundle"][type="hidden"]');
    
    console.log('Package input:', packageInput);
    console.log('Package price input:', packagePriceInput);
    
    if (packageInput) {
        if (packageInput.value === 'Custom Package') {
            console.log('Custom package detected');
            updateCostDisplay('To be determined', 0);
            return;
        } else {
            // Try to get price from multiple sources
            if (packagePriceInput) {
                packagePrice = parseFloat(packagePriceInput.value) || 0;
                console.log('Price from package_price input:', packagePrice);
            }
            
            if (packagePrice === 0 && packageInput.dataset.price) {
                packagePrice = parseFloat(packageInput.dataset.price) || 0;
                console.log('Price from data-price attribute:', packagePrice);
            }

            packageCost = packagePrice * numPersons;
            console.log('Final package price per person:', packagePrice);
            console.log('Total package cost:', packageCost);
        }
    } else {
        console.log('No package input found');
    }

    // Calculate services cost
    const services = document.querySelectorAll('input[name="options[]"]:checked');
    services.forEach(service => {
        switch(service.value) {
            case 'setup':
                servicesCost += 2000;
                break;
            case 'tables':
                servicesCost += 3500;
                break;
            case 'decoration':
                servicesCost += 5000;
                break;
        }
    });
    console.log('Services cost:', servicesCost);
    console.log('Total cost:', packageCost + servicesCost);

    updateCostDisplay(packageCost, servicesCost);
}

function updateCostDisplay(packageCost, servicesCost) {
    const packageDisplay = document.getElementById('packageCost');
    const servicesDisplay = document.getElementById('servicesCost');
    const totalDisplay = document.getElementById('totalAmount');

    if (packageCost === 'To be determined') {
        if (packageDisplay) packageDisplay.textContent = 'To be determined';
        if (totalDisplay) totalDisplay.textContent = 'To be determined';
        if (servicesDisplay) servicesDisplay.textContent = '₱' + servicesCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        const total = packageCost + servicesCost;
        if (packageDisplay) packageDisplay.textContent = '₱' + packageCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (servicesDisplay) servicesDisplay.textContent = '₱' + servicesCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); 
        if (totalDisplay) totalDisplay.textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

function checkForSpecialCases() {
    const numPersons = parseInt(document.getElementById('num_persons').value) || 0;
    const customPackage = document.querySelector('#package_custom:checked');
    
    return (numPersons < 50 || customPackage);
}

// Helper function to show alerts
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            $(alert).alert('close');
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'mb-3';
    
    const form = document.querySelector('form');
    form.insertBefore(container, form.firstChild);
    
    return container;
}

function initializeStep2Costs() {
    console.log('Step 2 initialization started');
    
    // Get the package cost from step 1 data
    const packagePriceInput = document.querySelector('input[name="package_price"]');
    const hiddenPackage = document.querySelector('input[name="menu_bundle"]');
    const hiddenNumPersons = document.querySelector('input[name="num_persons"]');
    
    console.log('Package price input:', packagePriceInput);
    console.log('Package price value:', packagePriceInput ? packagePriceInput.value : 'not found');
    console.log('Hidden package element:', hiddenPackage);
    console.log('Package name:', hiddenPackage ? hiddenPackage.value : 'not found');
    console.log('Package data-price:', hiddenPackage ? hiddenPackage.dataset.price : 'not found');
    console.log('Number of persons:', hiddenNumPersons ? hiddenNumPersons.value : 'not found');

    // Calculate total immediately
    calculateTotal();
}