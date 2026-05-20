export function WorkflowBuilderSkeleton() {
  return (
    <div className="animate-pulse space-y-6">
      <div className="h-10 w-72 rounded-lg bg-surface-overlay" />
      <div className="h-[420px] rounded-2xl border border-border bg-surface-raised" />
      <div className="grid gap-4 lg:grid-cols-3">
        <div className="h-48 rounded-xl bg-surface-overlay lg:col-span-2" />
        <div className="h-48 rounded-xl bg-surface-overlay" />
      </div>
    </div>
  );
}
