export const AUTH = '/login';

export const FAVORITE = {
  ADD: '/favorites',
  DEL: '/favorites/'
}

export const POPUP = {
  OFFER: {
    REGISTER: '/popup/offer/register',
  }
}

export const isCatalogPage = window.location.href.indexOf('catalog') > -1;
