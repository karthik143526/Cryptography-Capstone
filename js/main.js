document.addEventListener("DOMContentLoaded", function() {
    // ==================== Form Validations ====================
    const fileInput = document.getElementById("file");
    if (fileInput) {
        fileInput.addEventListener("change", function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 50 * 1024 * 1024; 
                if (file.size > maxSize) {
                    alert("File is too large. Maximum size is 50MB.");
                    fileInput.value = "";
                }
            }
        });
    }

    const regForm = document.getElementById("registerForm");
    if (regForm) {
        regForm.addEventListener("submit", function(e) {
            const pass = document.getElementById("password").value;
            const confirmPass = document.getElementById("confirm_password").value;
            if (pass !== confirmPass) {
                e.preventDefault();
                alert("Passwords do not match!");
            }
        });
    }

    // ==================== Drag & Drop Upload ====================
    const uploadArea = document.getElementById("uploadArea");
    if (uploadArea) {
        uploadArea.addEventListener("dragover", (e) => {
            e.preventDefault();
            uploadArea.classList.add("dragover");
        });

        uploadArea.addEventListener("dragleave", () => {
            uploadArea.classList.remove("dragover");
        });

        uploadArea.addEventListener("drop", (e) => {
            e.preventDefault();
            uploadArea.classList.remove("dragover");
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                displayFileInfo();
            }
        });

        uploadArea.addEventListener("click", () => {
            document.getElementById("file").click();
        });
    }

    // ==================== Animated Counters ====================
    const counterElements = document.querySelectorAll('.card .value');
    counterElements.forEach(el => {
        const finalValue = parseInt(el.textContent);
        if (!isNaN(finalValue) && finalValue > 0) {
            animateCounter(el, finalValue);
        }
    });

    // ==================== Active Sidebar Link ====================
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href').split('/').pop();
        if (href === currentPage) {
            link.classList.add('active');
        }
    });

    // ==================== Smooth Scrolling ====================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // ==================== Table Row Hover Effects ====================
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f0f4f8';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // ==================== Password Strength Indicator ====================
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', checkPasswordStrength);
    });

    console.log("✅ Secure File Storage - All scripts loaded successfully");
});

// ==================== Counter Animation ====================
function animateCounter(element, target) {
    let current = 0;
    const increment = Math.ceil(target / 30);
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = current;
    }, 50);
}

// ==================== Password Strength Check ====================
function checkPasswordStrength(e) {
    const password = e.target.value;
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Display indicator if needed
    const indicator = e.target.parentElement.querySelector('.password-strength');
    if (indicator) {
        const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength];
        indicator.textContent = `Strength: ${strengthText}`;
        indicator.style.color = ['#ef4444', '#f59e0b', '#eab308', '#84cc16', '#10b981'][strength];
    }
}

// ==================== File Display Info ====================
function displayFileInfo() {
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!fileInfo || !submitBtn) return;
    
    if (fileInput && fileInput.files.length) {
        const file = fileInput.files[0];
        const maxSize = 50 * 1024 * 1024;
        
        if (file.size > maxSize) {
            alert('File size exceeds 50 MB limit!');
            fileInput.value = '';
            fileInfo.style.display = 'none';
            submitBtn.style.display = 'none';
            return;
        }
        
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        fileInfo.style.display = 'block';
        submitBtn.style.display = 'block';
    }
}
