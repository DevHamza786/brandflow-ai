import { useScheduledPostsList } from '@/features/publishing/hooks/useScheduledPosts';
import { Badge } from '@/shared/components/ui/Badge';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { LoadingState } from '@/shared/components/feedback/LoadingState';
import { linkedInFeedUpdateUrl } from '@/shared/lib/linkedin';
import { cn } from '@/shared/lib/cn';

const STATUS_STYLES: Record<string, string> = {
  scheduled: 'border-amber-500/40 bg-amber-500/10 text-amber-200',
  queued: 'border-sky-500/40 bg-sky-500/10 text-sky-200',
  publishing: 'border-violet-500/40 bg-violet-500/10 text-violet-200',
  published: 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200',
  failed: 'border-red-500/40 bg-red-500/10 text-red-200',
  cancelled: 'border-slate-500/40 bg-slate-800/40 text-slate-400',
};

function statusLabel(status: string): string {
  if (status === 'publishing') {
    return 'Publishing…';
  }
  if (status === 'queued') {
    return 'Queued';
  }
  if (status === 'scheduled') {
    return 'Scheduled';
  }
  return status;
}

export function LinkedInPublishingPage() {
  const query = useScheduledPostsList();

  if (query.isLoading) {
    return <LoadingState message="Loading publish activity…" />;
  }

  if (query.isError) {
    return (
      <ErrorState
        error={query.error instanceof Error ? query.error.message : 'Failed to load'}
        onRetry={() => void query.refetch()}
      />
    );
  }

  const posts = query.data?.scheduled_posts ?? [];
  const polling = query.data?.in_flight ?? false;

  return (
    <div className="mx-auto max-w-3xl space-y-8 animate-fade-up">
      <header>
        <p className="text-xs font-medium uppercase tracking-widest text-accent">Publishing</p>
        <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white">LinkedIn posts</h1>
        <p className="mt-3 text-sm leading-relaxed text-slate-400">
          Queue runs on the API <code className="font-mono text-xs">scheduling</code> worker (Horizon). When a row is
          queued or publishing, this page refreshes automatically.
        </p>
        {polling && (
          <p className="mt-2 text-xs font-medium text-accent" role="status">
            Syncing… (activity in progress)
          </p>
        )}
      </header>

      <Card>
        <CardHeader>
          <h2 className="text-sm font-medium text-slate-300">Activity</h2>
          <p className="mt-1 text-xs text-slate-500">
            Posts from “Queue publish” on the LinkedIn integration page or{' '}
            <code className="font-mono">POST /api/v1/publish/linkedin</code>. After success,{' '}
            <code className="font-mono">provider_post_id</code> is stored for analytics.
          </p>
        </CardHeader>
        <CardBody>
          {posts.length === 0 ? (
            <p className="text-sm text-slate-500">
              No scheduled posts yet — open LinkedIn integrations and use “Queue publish”, or call the publish API.
            </p>
          ) : (
            <ul className="space-y-4">
              {posts.map((row) => {
                const feedUrl = linkedInFeedUpdateUrl(row.provider_post_id ?? row.linkedin_urn);
                const preview =
                  row.content_preview ??
                  (row.generated_output_id ? '(content from generated output)' : '—');

                return (
                  <li
                    key={row.id}
                    className="rounded-xl border border-border bg-surface-overlay/80 px-4 py-4 text-sm"
                  >
                    <div className="flex flex-wrap items-center justify-between gap-2">
                      <Badge
                        className={cn(
                          'capitalize',
                          STATUS_STYLES[row.status] ?? 'border-border text-slate-300',
                        )}
                      >
                        {statusLabel(row.status)}
                      </Badge>
                      <span className="font-mono text-[11px] text-slate-500">{row.id.slice(0, 13)}…</span>
                    </div>
                    <p className="mt-3 whitespace-pre-wrap text-slate-200">{preview}</p>
                    <dl className="mt-3 grid gap-1 text-xs text-slate-500">
                      <div className="flex flex-wrap gap-x-4 gap-y-1">
                        {row.scheduled_for && (
                          <span>
                            <span className="text-slate-600">When: </span>
                            {new Date(row.scheduled_for).toLocaleString()}
                          </span>
                        )}
                        {row.published_at && (
                          <span>
                            <span className="text-slate-600">Posted: </span>
                            {new Date(row.published_at).toLocaleString()}
                          </span>
                        )}
                        <span>
                          <span className="text-slate-600">Attempts: </span>
                          {row.attempt_count}
                        </span>
                      </div>
                      {row.provider_post_id && (
                        <div className="mt-1 break-all font-mono text-[11px] text-slate-400">
                          LinkedIn id: {row.provider_post_id}
                        </div>
                      )}
                      {feedUrl && (
                        <a
                          href={feedUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="mt-1 inline-block text-accent hover:underline"
                        >
                          Open on LinkedIn (best-effort)
                        </a>
                      )}
                      {row.status === 'failed' && row.error_details && (
                        <div className="mt-2 rounded border border-red-500/25 bg-red-500/5 p-2 text-red-200/90">
                          <pre className="max-h-28 overflow-auto whitespace-pre-wrap font-mono text-[11px]">
                            {JSON.stringify(row.error_details, null, 2)}
                          </pre>
                        </div>
                      )}
                    </dl>
                  </li>
                );
              })}
            </ul>
          )}
        </CardBody>
      </Card>
    </div>
  );
}
