function login() {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    fetch('src/php/verificar_login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `usuario=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "index.php";
            } else {
                const errorMessage = document.getElementById("error-message");
                errorMessage.textContent = data.message;
                errorMessage.style.display = "block";
            }
        })
        .catch(error => {
            console.error('Error en la solicitud:', error);
        });
}

document.getElementById("login-form").addEventListener("submit", function (event) {
    event.preventDefault(); // ✋ Detenemos el submit tradicional
    login();
});