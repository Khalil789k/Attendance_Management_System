
document.addEventListener('DOMContentLoaded', function() {

    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            submitBtn.disabled = true;
            

            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    }
    

    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
        

        if (control.value) {
            control.parentElement.classList.add('focused');
        }
    });
    

    const elements = document.querySelectorAll('.fade-in');
    elements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
    });
    

    const passwordField = document.getElementById('password');
    if (passwordField) {
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'btn btn-link position-absolute end-0 top-50 translate-middle-y';
        toggleBtn.style.zIndex = '10';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        
        passwordField.parentElement.style.position = 'relative';
        passwordField.parentElement.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', function() {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
}); 