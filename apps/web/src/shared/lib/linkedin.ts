/** Build a best-effort LinkedIn feed URL from a URN (e.g. urn:li:ugcPost:…). */
export function linkedInFeedUpdateUrl(urn: string | null | undefined): string | null {
  if (!urn || !urn.startsWith('urn:')) {
    return null;
  }
  return `https://www.linkedin.com/feed/update/${encodeURIComponent(urn)}`;
}
