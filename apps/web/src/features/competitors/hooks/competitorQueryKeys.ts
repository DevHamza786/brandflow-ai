export const competitorKeys = {
  all: ['competitors'] as const,
  list: () => [...competitorKeys.all, 'list'] as const,
  intelligence: (id: string) => [...competitorKeys.all, 'intelligence', id] as const,
  recommendations: (id: string) => [...competitorKeys.all, 'recommendations', id] as const,
};
