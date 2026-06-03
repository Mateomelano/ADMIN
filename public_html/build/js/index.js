function actualizarInformacion() {
    fetch(("src/php/get_info.php")) 
      .then((response) => response.json())
      .then((data) => {
        document.querySelector(".info-card:nth-child(1) p").textContent =
          data.totalProductos;
        document.querySelector(".info-card:nth-child(2) p").textContent =
          data.totalHabilitados;
        document.querySelector(".info-card:nth-child(3) p").textContent =
          data.totalDeshabilitados;
        document.querySelector(
          ".info-card:nth-child(4) p"
        ).textContent = `${data.productoMasCaro.nombre} ($${data.productoMasCaro.precio})`;
        document.querySelector(
          ".info-card:nth-child(5) p"
        ).textContent = `${data.productoMasBarato.nombre} ($${data.productoMasBarato.precio})`;
      })
      .catch((error) => console.error("Error al obtener la información:", error));
  }
  
  setInterval(actualizarInformacion, 5000);

document.addEventListener("DOMContentLoaded", function () {
    if (sessionStorage.getItem("loggedIn")) {
      document.getElementById("login-screen").style.display = "none";
      document.getElementById("main-content").style.display = "block";
    }
  });
  
  function login() {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    if (username === "1" && password === "1") {
      sessionStorage.setItem("loggedIn", true);
      document.getElementById("login-screen").style.display = "none";
      document.getElementById("main-content").style.display = "block";
    } else {
      document.getElementById("error-message").style.display = "block";
    }
  }
  