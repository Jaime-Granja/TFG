window.onload = function () {
  let logOut = document.getElementById("logOut");
  let newCampaign = document.getElementById("newCampaignButton");
  let campaignButtons = document.querySelectorAll(".campaign .mas");
  campaignButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const campaignId = button.dataset.campaignId;
      if (campaignId) {
        window.location.href = `campaign.php?id=${campaignId}`;
      }
    });
  });

  logOut.addEventListener("click", atras);
  newCampaign.addEventListener("click", newCampaignFunction);

  function newCampaignFunction() {
    window.location.href = "../front/public/newCampaign.html";
  }

  function atras() {
    window.location.href = "logout.php";
  }

  function campaignFunction() {
    window.location.href = "campaign.php";
  }
};
