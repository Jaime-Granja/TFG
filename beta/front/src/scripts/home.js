window.onload = function () {
  let logOut = document.getElementById("logOut");
  let newCampaign = document.getElementById("newCampaignButton");
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
        window.location.href = `viewCharacterDemo.php?id=${characterId}`;
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
