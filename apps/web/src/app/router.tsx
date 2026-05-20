import { createBrowserRouter, Navigate } from 'react-router-dom';
import { AppShell } from '@/shared/components/layout/AppShell';
import { BrandProfilePage } from '@/pages/BrandProfilePage';
import { GenerateHooksPage } from '@/pages/GenerateHooksPage';
import { WorkflowStatusPage } from '@/pages/WorkflowStatusPage';
import { ResultsViewerPage } from '@/pages/ResultsViewerPage';
import { LinkedInIntegrationPage } from '@/pages/LinkedInIntegrationPage';
import { LinkedInPublishingPage } from '@/pages/LinkedInPublishingPage';
import { AnalyticsDashboardPage } from '@/pages/AnalyticsDashboardPage';
import { CompetitorDashboardPage } from '@/pages/CompetitorDashboardPage';
import { OptimizationDashboardPage } from '@/pages/OptimizationDashboardPage';
import { AutonomousDashboardPage } from '@/pages/AutonomousDashboardPage';
import { WorkflowBuilderPage } from '@/pages/WorkflowBuilderPage';

export const router = createBrowserRouter([
  {
    path: '/',
    element: <AppShell />,
    children: [
      { index: true, element: <Navigate to="/generate" replace /> },
      { path: 'brand', element: <BrandProfilePage /> },
      { path: 'integrations', element: <LinkedInIntegrationPage /> },
      { path: 'settings/integrations', element: <LinkedInIntegrationPage /> },
      { path: 'integrations/posts', element: <LinkedInPublishingPage /> },
      { path: 'analytics', element: <AnalyticsDashboardPage /> },
      { path: 'competitors', element: <CompetitorDashboardPage /> },
      { path: 'optimization', element: <OptimizationDashboardPage /> },
      { path: 'autonomous', element: <AutonomousDashboardPage /> },
      { path: 'workflows', element: <WorkflowBuilderPage /> },
      { path: 'generate', element: <GenerateHooksPage /> },
      { path: 'runs/:id', element: <WorkflowStatusPage /> },
      { path: 'results/:id', element: <ResultsViewerPage /> },
    ],
  },
]);
