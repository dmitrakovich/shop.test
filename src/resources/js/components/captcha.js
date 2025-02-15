import { load } from 'recaptcha-v3';

export default async function сaptcha() {
  const recaptcha = await load('6LfzAtgqAAAAALpHO4t9LQ1AtkB9kkNf5s1jddz8', {
    autoHideBadge: true
  });
  const token = await recaptcha.execute('submit');

  return token;
}
