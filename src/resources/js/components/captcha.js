import { load } from 'recaptcha-v3';

export default async function сaptcha() {
  const recaptcha = await load('6Ld3C6kcAAAAAF99FvSsKlBlMGu8uhP9TO_fBY-V');
  const token = await recaptcha.execute('submit');

  return token;
}
