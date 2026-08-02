import { BACKEND_ORIGIN } from './env.js'

/** Full URL for a user's uploaded avatar, or null if they have none. */
export function avatarUrl(user) {
  return user?.avatarUrl ? `${BACKEND_ORIGIN}/${user.avatarUrl}` : null
}
