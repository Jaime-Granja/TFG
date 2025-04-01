window.onload = function () {
  let logOut = document.getElementById("logOut");
  logOut.addEventListener("click", atras);
  let newCampaign = document.getElementById("newCampaignButton");
  newCampaign.addEventListener("click", campaign);

  function campaign() {
    window.location.href = "newCampaign.html";
  }

  function atras() {
    window.location.href = "index.html";
  }
};
