const BACKEND_ORIGIN = 'http://localhost:8000'

/** Full URL for a user's uploaded avatar, or null if they have none. */
export function avatarUrl(user) {
  return user?.avatarUrl ? `${BACKEND_ORIGIN}/${user.avatarUrl}` : null
}
