<?php

namespace anytech\googlereviews\Tasks;

use anytech\googlereviews\Elements\GoogleReview as GoogleReviewElement;
use anytech\googlereviews\Models\GoogleReview;
use anytech\googlereviews\Services\GoogleReviewsClient;
use RuntimeException;
use SilverStripe\CronTask\Interfaces\CronTask;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class GoogleReviewsSyncTask extends BuildTask implements CronTask {
    protected static string $commandName = 'google-reviews-sync';

    protected string $title = 'Google Reviews Sync';

    protected static string $description = 'Fetches reviews from Google Places and stores them as DataObjects.';

    private static string $schedule = '0 3 * * *';

    public function getSchedule() {
        return $this->config()->get('schedule');
    }

    public function process() {
        $this->sync();
    }

    protected function execute(InputInterface $input, PolyOutput $output): int {
        try {
            $count = $this->sync();
        } catch (RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln("Imported: {$count}");
        return Command::SUCCESS;
    }

    private function sync(): int {
        $cfg = SiteConfig::current_site_config();
        $min = (int)$cfg->GoogleReviewsMinRating;
        $rows = GoogleReviewsClient::create()->fetchReviews();

        $count = 0;
        foreach ($rows as $r) {
            $rating = (int)($r['rating'] ?? 0);
            if ($min > 0 && $rating < $min) {
                continue;
            }

            $id = (string)($r['name'] ?? sha1(json_encode($r)));

            foreach (GoogleReviewElement::get() as $element) {
                $gr = GoogleReview::get()->filter([
                    'GoogleReviewID' => $id,
                    'ElementID' => $element->ID
                ])->first();

                if (!$gr) {
                    $gr = GoogleReview::create();
                    $gr->GoogleReviewID = $id;
                    $gr->ElementID = $element->ID;
                }

                $gr->AuthorName = (string)($r['authorAttribution']['displayName'] ?? '');
                $gr->AuthorURL = (string)($r['authorAttribution']['uri'] ?? '');
                $gr->AuthorPhotoURL = (string)($r['authorAttribution']['photoUri'] ?? '');
                $gr->Rating = $rating;
                $gr->Text = (string)($r['text']['text'] ?? ($r['originalText']['text'] ?? ''));
                $gr->RelativeTime = (string)($r['relativePublishTimeDescription'] ?? '');
                $gr->TimeUnix = isset($r['publishTime']) ? strtotime($r['publishTime']) : time();
                $gr->Language = (string)($r['originalText']['languageCode'] ?? '');
                $gr->PlaceID = (string)$cfg->GooglePlaceID;
                $gr->write();

                $count++;
            }
        }

        return $count;
    }
}
