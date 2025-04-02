window.onload = function () {
  let logOut = document.getElementById("logOut");
  let newCampaign = document.getElementById("newCampaignButton");
  let campaign = document.getElementById("campaign1");

  logOut.addEventListener("click", atras);
  newCampaign.addEventListener("click", newCampaignFunction);
  campaign.addEventListener("click", campaignFunction);

  function newCampaignFunction() {
    window.location.href = "newCampaign.html";
  }

  function atras() {
    window.location.href = "index.html";
  }

  function campaignFunction() {
    window.location.href = "campaign.html";
  }
};
