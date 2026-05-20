import { useQuery, useMutation } from '@tanstack/react-query';
import { useCallback, useMemo, useState } from 'react';
import { Link, useLocation, useSearchParams } from 'react-router-dom';
import { apiGet, apiPost } from '@/shared/api/client';
import { Button } from '@/shared/components/ui/Button';
import { Card, CardBody, CardHeader } from '@/shared/components/ui/Card';
import { ErrorState } from '@/shared/components/feedback/ErrorState';
import { LoadingState } from '@/shared/components/feedback/LoadingState';
import { env } from '@/shared/config/env';
import { cn } from '@/shared/lib/cn';
import { useToast } from '@/shared/providers/ToastProvider';

type LinkedInIntegrationItem = {
  id: string;
  status: string;
  linkedin_member_id: string | null;
  has_access_token: boolean;
  has_refresh_token: boolean;
  is_token_expired: boolean;
  token_expires_at: string | null;
  connected_at: string | null;
  last_error: string | null;
};

type ScheduledPostQueued = {
  id: string;
  status: string;
  scheduled_for: string | null;
};

type ConnectPayload = {
  authorization_url: string;
  state: string;
  expires_at: string;
};

async function fetchIntegrations(): Promise<LinkedInIntegrationItem[]> {
  const res = await apiGet<{ integrations: LinkedInIntegrationItem[] }>(
    '/integrations/linkedin',
  );
  return res.integrations ?? [];
}

async function startLinkedInOAuth(redirectAfter: string): Promise<void> {
  const params = new URLSearchParams({
    redirect_after: redirectAfter,
  });
  const data = await apiGet<ConnectPayload>(
    `/integrations/linkedin/connect?${params.toString()}`,
  );
  window.location.assign(data.authorization_url);
}

export function LinkedInIntegrationPage() {
  const [searchParams] = useSearchParams();
  const location = useLocation();
  const status = searchParams.get('linkedin');
  const integrationId = searchParams.get('integration_id');
  const message = searchParams.get('message');

  /** OAuth success/error return URL — must match the page you're on (/integrations or /settings/integrations). */
  const redirectAfterUrl = useMemo(
    () => `${window.location.origin}${location.pathname}`,
    [location.pathname],
  );

  const listQuery = useQuery({
    queryKey: ['integrations', 'linkedin'],
    queryFn: fetchIntegrations,
  });

  const toast = useToast();
  const [connectLoading, setConnectLoading] = useState(false);
  const [publishBody, setPublishBody] = useState(
    'Quick test post from BrandFlow — safe to delete.',
  );

  const publishMutation = useMutation({
    mutationFn: async () => {
      const rows = listQuery.data;
      const integration = rows?.find((r) => r.status === 'connected') ?? rows?.[0];
      if (!integration) {
        throw new Error('Connect LinkedIn first (need at least one integration).');
      }
      return apiPost<ScheduledPostQueued>('/publish/linkedin', {
        linkedin_integration_id: integration.id,
        content: publishBody,
      });
    },
    onSuccess: (data) => {
      toast.push(
        `Publish queued: ${data.id} (${data.status}). Check Horizon (scheduling queue) or Postgres scheduled_posts.`,
        'success',
      );
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === 'object' && 'message' in err
          ? String((err as { message: string }).message)
          : 'Request failed';
      toast.push(message, 'error');
    },
  });

  const onConnect = useCallback(async () => {
    setConnectLoading(true);
    try {
      await startLinkedInOAuth(redirectAfterUrl);
    } catch (err: unknown) {
      const message =
        err && typeof err === 'object' && 'message' in err
          ? String((err as { message: string }).message)
          : 'Could not start LinkedIn OAuth';
      toast.push(message, 'error');
      setConnectLoading(false);
    }
  }, [redirectAfterUrl, toast]);

  return (
    <div className="mx-auto max-w-2xl space-y-6 animate-fade-up">
      <div>
        <p className="text-xs font-medium uppercase tracking-widest text-accent">Integrations</p>
        <h1 className="mt-2 text-2xl font-semibold tracking-tight text-white">LinkedIn (OAuth test)</h1>
        <p className="mt-2 text-sm text-slate-400">
          Dev-only surface to start OAuth. Uses workspace{' '}
          <code className="rounded bg-surface-overlay px-1.5 py-0.5 font-mono text-xs text-slate-300">
            {env.workspaceId.slice(0, 8)}…
          </code>{' '}
          from <code className="font-mono text-xs">VITE_DEFAULT_WORKSPACE_ID</code>. Callback hits the API (
          <code className="font-mono text-xs">/integrations/linkedin/callback</code>
          ), then you return here via <code className="font-mono text-xs">redirect_after</code>.
        </p>
        <p className="mt-2">
          <Link to="/integrations/posts" className="text-sm font-medium text-accent hover:underline">
            View publish queue &amp; posted activity →
          </Link>
        </p>
      </div>

      {status === 'connected' && (
        <div
          className="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"
          role="status"
        >
          LinkedIn flow completed
          {integrationId ? (
            <>
              {' '}
              — integration <span className="font-mono text-xs">{integrationId}</span>
            </>
          ) : null}
        </div>
      )}
      {status === 'error' && (
        <div className="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
          <p>Something went wrong</p>
          {message ? (
            <p className="mt-2 opacity-90">{decodeURIComponent(message)}</p>
          ) : null}
          {message?.includes('scope') ? (
            <p className="mt-3 text-xs text-slate-400">
              Likely fix: in API <code className="font-mono">.env</code> set{' '}
              <code className="font-mono">LINKEDIN_SCOPES=openid,profile,email</code> (drop{' '}
              <code className="font-mono">w_member_social</code> until LinkedIn approves that
              product), then restart API and try again.
            </p>
          ) : null}
          {message?.includes('workspaces') || message?.includes('workspace_id') ? (
            <p className="mt-3 text-xs text-slate-400">
              This workspace UUID must exist in Postgres <code className="font-mono">workspaces</code>. With
              Docker, set <code className="font-mono">SEED_DEV_DEFAULT_WORKSPACE=true</code> and{' '}
              <code className="font-mono">PBOS_DEV_WORKSPACE_ID</code> to match{' '}
              <code className="font-mono">VITE_DEFAULT_WORKSPACE_ID</code>, then restart the API container (or run{' '}
              <code className="font-mono">php artisan db:seed --class=DevDefaultWorkspaceSeeder</code>
              ).
            </p>
          ) : null}
        </div>
      )}

      <Card>
        <CardHeader>
          <h2 className="text-sm font-medium text-slate-300">Connect</h2>
          <p className="mt-1 text-xs text-slate-500">
            API must have <code className="font-mono">LINKEDIN_CLIENT_ID</code> /{' '}
            <code className="font-mono">SECRET</code> and redirect URL matching the API (
            e.g. <code className="font-mono">http://localhost:8080/integrations/linkedin/callback</code>
            ).
          </p>
        </CardHeader>
        <CardBody className="space-y-3">
          <Button
            type="button"
            loading={connectLoading}
            onClick={() => void onConnect()}
          >
            {connectLoading ? 'Redirecting…' : 'Connect LinkedIn'}
          </Button>
          <p className="text-xs text-slate-500">
            Success redirect:{' '}
            <span className="font-mono text-slate-400">{redirectAfterUrl}</span>{' '}
            (set <code className="font-mono">INTEGRATIONS_SUCCESS_REDIRECT</code> on API if you want a different URL)
          </p>
        </CardBody>
      </Card>

      <Card>
        <CardHeader>
          <h2 className="text-sm font-medium text-slate-300">Queue LinkedIn publish (dev)</h2>
          <p className="mt-1 text-xs text-slate-500">
            Calls <code className="font-mono">POST /api/v1/publish/linkedin</code> — queues{' '}
            <code className="font-mono">PublishLinkedInPostJob</code> on the{' '}
            <code className="font-mono">scheduling</code> queue. Requires a connected integration and a valid
            token/scopes for posting (LinkedIn may still reject in sandbox).
          </p>
        </CardHeader>
        <CardBody className="space-y-3">
          <label className="block text-xs font-medium text-slate-400" htmlFor="publish-body">
            Post text
          </label>
          <textarea
            id="publish-body"
            className={cn(
              'min-h-[100px] w-full rounded-lg border border-border bg-surface-overlay px-3 py-2 text-sm text-slate-200',
              'placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-accent/40',
            )}
            value={publishBody}
            onChange={(e) => setPublishBody(e.target.value)}
            maxLength={8000}
          />
          <Button
            type="button"
            disabled={publishMutation.isPending || listQuery.isLoading || (listQuery.data?.length ?? 0) === 0}
            onClick={() => void publishMutation.mutate()}
          >
            {publishMutation.isPending ? 'Queueing…' : 'Queue publish job'}
          </Button>
          {(listQuery.isSuccess && listQuery.data.length === 0) && (
            <p className="text-xs text-amber-400/90">Connect LinkedIn above before queuing a publish.</p>
          )}
        </CardBody>
      </Card>

      <Card>
        <CardHeader>
          <h2 className="text-sm font-medium text-slate-300">Workspace integrations</h2>
        </CardHeader>
        <CardBody>
          {listQuery.isLoading && <LoadingState message="Loading…" />}
          {listQuery.isError && (
            <ErrorState
              error={
                listQuery.error instanceof Error ? listQuery.error.message : 'Failed to load integrations'
              }
              onRetry={() => void listQuery.refetch()}
            />
          )}
          {listQuery.isSuccess &&
            (listQuery.data.length === 0 ? (
              <p className="text-sm text-slate-500">No LinkedIn integration for this workspace yet.</p>
            ) : (
              <ul className="space-y-3">
                {listQuery.data.map((row) => (
                  <li
                    key={row.id}
                    className={cn(
                      'rounded-lg border border-border bg-surface-overlay px-4 py-3 text-sm',
                    )}
                  >
                    <div className="flex flex-wrap items-center justify-between gap-2">
                      <span className="font-medium text-slate-200">{row.status}</span>
                      <span className="font-mono text-xs text-slate-500">{row.id}</span>
                    </div>
                    {row.linkedin_member_id && (
                      <p className="mt-1 text-xs text-slate-400">
                        Member: <span className="font-mono">{row.linkedin_member_id}</span>
                      </p>
                    )}
                    <p className="mt-2 text-xs text-slate-500">
                      access: {row.has_access_token ? 'yes' : 'no'} · refresh:{' '}
                      {row.has_refresh_token ? 'yes' : 'no'}
                      {row.is_token_expired ? ' · token expired' : ''}
                    </p>
                    {row.last_error && (
                      <p className="mt-1 text-xs text-amber-400/90">{row.last_error}</p>
                    )}
                  </li>
                ))}
              </ul>
            ))}
        </CardBody>
      </Card>
    </div>
  );
}
