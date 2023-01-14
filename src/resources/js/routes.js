const FAVORITE = {
  ADD: '/favorites',
  DEL: '/favorites/'
}

const POPUP = {
  OFFER: {
    REGISTER: '/popup/offer/register',
  }
}

const isCatalogPage = window.location.href.indexOf('catalog') > -1;

export { FAVORITE, POPUP, isCatalogPage };
