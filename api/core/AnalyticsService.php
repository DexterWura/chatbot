<?php

namespace Chatbot\Core;

/**
 * Analytics Service - Tracks usage statistics
 */
class AnalyticsService {
    private ConversationRepository $repository;
    private string $analyticsFile;
    private ?LoggerInterface $logger;

    public function __construct(
        ConversationRepository $repository,
        string $analyticsFile,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->analyticsFile = $analyticsFile;
        $this->logger = $logger;
    }

    public function trackRequest(string $provider, string $model, int $tokensUsed = 0): void {
        $data = $this->loadAnalytics();
        
        $date = date('Y-m-d');
        if (!isset($data[$date])) {
            $data[$date] = [];
        }

        if (!isset($data[$date][$provider])) {
            $data[$date][$provider] = [
                'requests' => 0,
                'tokens' => 0,
                'models' => []
            ];
        }

        $data[$date][$provider]['requests']++;
        $data[$date][$provider]['tokens'] += $tokensUsed;
        
        if (!isset($data[$date][$provider]['models'][$model])) {
            $data[$date][$provider]['models'][$model] = 0;
        }
        $data[$date][$provider]['models'][$model]++;

        $this->saveAnalytics($data);
    }

    public function getStats(int $days = 7): array {
        $data = $this->loadAnalytics();
        $cutoff = date('Y-m-d', strtotime("-$days days"));
        
        $stats = [
            'total_requests' => 0,
            'total_tokens' => 0,
            'by_provider' => [],
            'by_model' => []
        ];

        foreach ($data as $date => $dayData) {
            if ($date < $cutoff) continue;

            foreach ($dayData as $provider => $providerData) {
                if (!isset($stats['by_provider'][$provider])) {
                    $stats['by_provider'][$provider] = [
                        'requests' => 0,
                        'tokens' => 0
                    ];
                }

                $stats['by_provider'][$provider]['requests'] += $providerData['requests'];
                $stats['by_provider'][$provider]['tokens'] += $providerData['tokens'];
                $stats['total_requests'] += $providerData['requests'];
                $stats['total_tokens'] += $providerData['tokens'];

                foreach ($providerData['models'] ?? [] as $model => $count) {
                    if (!isset($stats['by_model'][$model])) {
                        $stats['by_model'][$model] = 0;
                    }
                    $stats['by_model'][$model] += $count;
                }
            }
        }

        return $stats;
    }

    private function loadAnalytics(): array {
        if (!file_exists($this->analyticsFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->analyticsFile), true) ?: [];
    }

    private function saveAnalytics(array $data): void {
        file_put_contents($this->analyticsFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}
