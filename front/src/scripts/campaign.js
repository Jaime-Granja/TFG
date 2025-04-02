window.onload = function () {
  let menu = document.getElementById("menuHamburguesa");
  let fondo = document.getElementById("contenedor");
  let margen = document.getElementById("margin");
  let botones = document.getElementById("menuHamburguesaBotones");
  let userBotton = document.getElementById("userProfile");
  let goBack = document.getElementById("goBack");
  let logOut = document.getElementById("logOut");
  let editCampaign = document.getElementById("campaignButton");
  let contenido = document.getElementById("contenido");
  let campaignInfo = document.getElementById("campaignInfo")
  let campaignForm = document.getElementById("campaignForm");



  menu.addEventListener("click", abrirMenu);
  document.addEventListener("click", function (event) {
    cerrarMenu(event);
  });
  goBack.addEventListener("click", goBackFunction);
  logOut.addEventListener("click", logOutFunction);
  userBotton.addEventListener("click", user);
  editCampaign.addEventListener("click", editCampaignFunction);

  function abrirMenu() {
    botones.style.display = "flex";
    menu.style.display = "none";
    fondo.style.opacity = "50%";
    margen.style.height = window.getComputedStyle(fondo).height;
    margen.style.backgroundColor = "black";
    margen.style.opacity = "90%";
  }

  function cerrarMenu(event) {
    if (!margen.contains(event.target) && event.target !== menu) {
      botones.style.display = "none";
      menu.style.display = "block";
      fondo.style.opacity = "100%";
      margen.style.backgroundColor = "transparent";
    }
  }

  function editCampaignFunction() {
    if (editCampaign.textContent == "Editar") {
      contenido.style.display = "none";
      editCampaign.textContent = "Volver a Campaña";
      campaignForm.style.display = "block"
    } else {
      contenido.style.display = "block";
      editCampaign.textContent = "Editar";
      campaignForm.style.display = "none"
    }
  }

  function user() {
    window.location.href = "user.html";
  }
  function goBackFunction() {
    window.location.href = "home.html";
  }
  function logOutFunction() {
    window.location.href = "index.html";
  }
};
