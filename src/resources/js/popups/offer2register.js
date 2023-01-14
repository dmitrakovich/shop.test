import { isCatalogPage, POPUP } from "../routes";

const POPUP_KEY = 'POPUP_OFFER_REGISTER_CLOSE';
const delay = 120000; // 2 min

if (isCatalogPage && !window.User && !popupShown()) {
  setTimeout(() => {
    axios.get(POPUP.OFFER.REGISTER).then(
      response => $.fancybox.open(response.data, {
        smallBtn: false,
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






