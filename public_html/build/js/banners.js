document.addEventListener("DOMContentLoaded", function () {
    cargarBanners();
    cargarBannersCel();
    
    document.getElementById("uploadBanner").addEventListener("change", function (event) {
      const file = event.target.files[0];
      const formData = new FormData();
      formData.append("imagen", file);
  
      fetch("src/php/subir_banner.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            cargarBanners();
          } else {
            alert(data.message);
          }
        });
    });
  });
  
  function cargarBanners() {
    fetch("src/php/get_banners.php")
      .then((response) => response.json())

      .then((data) => {
        const bannerContainer = document.getElementById("bannerContainer");
        bannerContainer.innerHTML = "";
  
        data.forEach((banner) => {
          const bannerDiv = document.createElement("div");
          bannerDiv.className = "banner-item";
          bannerDiv.innerHTML = `
            <img src="${banner.url}" alt="Banner" width="300">
            <button onclick="eliminarBanner(${banner.id}, '${banner.url}')">Eliminar</button>
          `;
          bannerContainer.appendChild(bannerDiv);
        });
      });
  }
  
  function eliminarBanner(id, url) {
    if (!confirm("¿Estás seguro de que deseas eliminar este banner?")) return;
  
    fetch("src/php/eliminar_banner.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${id}&url=${encodeURIComponent(url)}`
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        cargarBanners();
      } else {
        alert("Error al eliminar el banner.");
      }
    });
  }


  document.getElementById("uploadBannerCel").addEventListener("change", function (event) {
  const file = event.target.files[0];
  const formData = new FormData();
  formData.append("imagen", file);

  fetch("src/php/subir_bannerCel.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        cargarBannersCel();
      } else {
        alert(data.message);
      }
    });
});

function cargarBannersCel() {
  fetch("src/php/get_bannerCel.php")
    .then((response) => response.json())
    .then((data) => {
      const bannerCelContainer = document.getElementById("bannerCelContainer");
      bannerCelContainer.innerHTML = "";

      data.forEach((banner) => {
        const bannerDiv = document.createElement("div");
        bannerDiv.className = "banner-item";
        bannerDiv.innerHTML = `
          <img src="${banner.url}" alt="BannerCel" width="300">
          <button onclick="eliminarBannerCel(${banner.id}, '${banner.url}')">Eliminar</button>
        `;
        bannerCelContainer.appendChild(bannerDiv);
      });
    });
}

function eliminarBannerCel(id, url) {
  if (!confirm("¿Estás seguro de que deseas eliminar este banner de celular?")) return;

  fetch("src/php/eliminar_bannerCel.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&url=${encodeURIComponent(url)}`
  })
  .then((response) => response.json())
  .then((data) => {
    if (data.success) {
      cargarBannersCel();
    } else {
      alert("Error al eliminar el banner de celular.");
    }
  });
}

