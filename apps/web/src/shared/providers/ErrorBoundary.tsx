import { Component, type ErrorInfo, type ReactNode } from 'react';
import { ErrorState } from '@/shared/components/feedback/ErrorState';

type Props = { children: ReactNode };
type State = { error: Error | null };

export class ErrorBoundary extends Component<Props, State> {
  state: State = { error: null };

  static getDerivedStateFromError(error: Error): State {
    return { error };
  }

  componentDidCatch(error: Error, info: ErrorInfo): void {
    console.error('UI error boundary', error, info);
  }

  render() {
    if (this.state.error) {
      return (
        <div className="mx-auto max-w-lg py-16">
          <ErrorState
            error={this.state.error}
            onRetry={() => this.setState({ error: null })}
          />
        </div>
      );
    }
    return this.props.children;
  }
}
