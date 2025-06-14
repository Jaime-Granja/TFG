window.onload = function () {
  let userBotton = document.getElementById("userProfile");
  let logOut = document.getElementById("logOut");
  let html = document.getElementById("html");
  let newCampaign = document.getElementById("newCampaignButton");
  let newCharacter = document.getElementById("createSheet");
  let campaignButtons = document.querySelectorAll(".campaign .mas");
  let characterButtons = document.querySelectorAll(".sheet .mas");
  campaignButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const campaignId = button.dataset.campaignId;
      if (campaignId) {
        window.location.href = `campaign.php?id=${campaignId}`;
      }
    });
  });

  characterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const characterId = button.dataset.characterId;
      if (characterId) {
        window.location.href = `viewCharacter.php?id=${characterId}`;
      }
    });
  });
  userBotton.addEventListener("click", user);
  logOut.addEventListener("click", atras);
  newCampaign.addEventListener("click", newCampaignFunction);
  newCharacter.addEventListener("click", newCharacterFunction);

  let menu = document.getElementById("menuHamburguesa");
  let botones = document.getElementById("menuHamburguesaBotones");
  let margen = document.getElementById("margin");
  let fondo = document.getElementById("body");
  menu.addEventListener("click", abrirMenu);
  document.addEventListener("click", function (event) {
    cerrarMenu(event);
  });

  function abrirMenu() {
    botones.style.display = "flex";
    menu.style.display = "none";
    margen.style.height = window.getComputedStyle(html).height;
    margen.style.backgroundColor = "#242848";
    margen.style.opacity = "90%";
  }

  function cerrarMenu(event) {
    if (!margen.contains(event.target) && event.target !== menu) {
      botones.style.display = "none";
      menu.style.display = "block";
      margen.style.height = 0;
      margen.style.backgroundColor = "transparent";
    }
  }
  function newCampaignFunction() {
    window.location.href = "../front/newCampaign.php";
  }

  function newCharacterFunction() {
    window.location.href = "../front/createCharacterView.php";
  }

  function atras() {
    window.location.href = "../back/logout.php";
  }

  function user() {
    window.location.href = "../front/user.php";
  }
  const popup = document.getElementById("popup");
  
  if (popup && popup.textContent.trim() !== "") {
    popup.classList.add("show");
    setTimeout(() => {
      popup.classList.remove("show");
      setTimeout(() => (popup.style.display = "none"), 500);
    }, 4000);
  }
};
