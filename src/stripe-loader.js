/**
 * Usage:
 *
 * loadStripeApi( myPublicApiKey )
 *  .then(stripe => stripe.card.createToken )
 *  .then( ({error,token}) => ... )
 */

const stripeScriptUrl = 'https://js.stripe.com/'

let stripeLoaderPromise = null
const loadedPackages = new Map()

export function getStripeLoader (version = 3) {
  // If already included in the page:
  if (window.Stripe) {
    return Promise.resolve(window.Stripe)
  }
  if (!stripeLoaderPromise) {
    stripeLoaderPromise = new Promise((resolve,reject) => {
      const script = document.createElement('script')
      script.type = 'text/javascript'
      script.onload = () => resolve(window.Stripe)
      script.onerror = (err) => reject(err)
      script.src = stripeScriptUrl + `v${version}/`
      document.body.appendChild(script)
    })
  }
  return stripeLoaderPromise
}

/**
 * @todo - See upgrade guide - https://stripe.com/docs/stripe-js/elements/migrating
 * @param  {[type]} apiKey [description]
 * @return {[type]}        [description]
 */
export default async function loadStripeApi(apiKey = null, version = 3) {
  return getStripeLoader(version)
  .then(s => {
    if (!apiKey || typeof apiKey !== 'string') {throw new Error('Stripe is missing a valid API key')}
    if (version < 3) {
      s.setPublishableKey(apiKey);
      return window.Stripe;
    } else {
      if (!window.stripe_instance) {
        window.stripe_instance = Stripe(apiKey);
      }
      return window.stripe_instance;
    }
  })
  .catch(err => Promise.reject(err))
}
