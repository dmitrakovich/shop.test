import { POPUP } from "../routes";
import Cookies from "../cookies";

const POPUP_KEY = "new-site-popup-shown";

if (!window.User && !newSitePopupShown()) {
  axios.get(POPUP.OFFER.NEW_SITE).then((response) =>
    $.fancybox.open(response.data, {
      smallBtn: false,
      autoFocus: false,
      afterClose: () => saveNewSitePopupShowning(),
    }),
  );
}

function newSitePopupShown() {
  return !!Cookies.get(POPUP_KEY);
}

function saveNewSitePopupShowning() {
  return Cookies.set(POPUP_KEY, 1, 0.02);
}
