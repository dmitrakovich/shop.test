import { SESSION_TIME_KEY } from "../constants";
import { isCatalogPage, POPUP } from "../routes";

const POPUP_KEY = 'POPUP_OFFER_REGISTER_CLOSE';
const sessionTime = sessionStorage.getItem(SESSION_TIME_KEY) ?? 0;
const delay = (120 - sessionTime) * 1000; // 2 min

if (isCatalogPage && !window.User && !popupShown()) {
  setTimeout(() => {
    axios.get(POPUP.OFFER.REGISTER).then(
      response => $.fancybox.open(response.data, {
        smallBtn: false,
        autoFocus: false,
        afterClose: () => savePopupShowning()
      })
    );
  }, delay);
}

function popupShown() {
  return !!sessionStorage.getItem(POPUP_KEY);
}

function savePopupShowning() {
  return sessionStorage.setItem(POPUP_KEY, 1);
}






