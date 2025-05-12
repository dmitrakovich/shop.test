export const AUTH = '/login';

export const FAVORITE = {
  ADD: '/favorites',
  DEL: '/favorites/'
}

export const POPUP = {
  OFFER: {
    REGISTER: '/popup/offer/register',
    NEW_SITE: '/popup/new-site',
  }
}

export const CART = {
  APPLY_PROMOCODE: 'cart/apply-promocode',
}

export const isCatalogPage = window.location.href.indexOf('catalog') > -1;
export const isCartPage = window.location.href.indexOf('cart') > -1;
