<?php

namespace App\Services;

use Nitotm\Eld\EldMode;
use Nitotm\Eld\EldScheme;
use Nitotm\Eld\LanguageDetector;

/**
 * Detects whether free-text input is written in a Philippine language.
 *
 * Philippine languages (Tagalog, Cebuano, Ilocano, Hiligaynon, Waray, Bicolano,
 * etc.) are written with the plain Latin alphabet, so character/pattern rules
 * cannot distinguish them from English. This service uses a trained n-gram
 * language detector instead and is intentionally conservative: it only reports
 * a Philippine language when the detector is clearly more confident in it than
 * in English, so legitimate English submissions are not rejected.
 */
class LanguageDetectionService
{
    /**
     * Detector language codes that indicate a Philippine language.
     *
     * - tl : Tagalog / Filipino
     * - ms : Malay (Cebuano, Hiligaynon, Waray and Bicolano are frequently
     *        scored as Malay because they share much of its vocabulary)
     * - id : Indonesian (same reason; Indonesian and Malay are near-identical)
     *
     * The detector has no dedicated model for the smaller Philippine
     * languages, so they surface as one of these codes.
     */
    private const PHILIPPINE_LANGUAGES = ['tl', 'ms', 'id'];

    /**
     * Minimum confidence gap the detected language must have over English
     * before the text is considered to be a Philippine language.
     *
     * Tuned so that English, Spanish, French, German and Chinese samples are
     * never flagged (zero false positives), while clearly Philippine-language
     * text is still caught. Raising this value reduces false positives further
     * but lets more Tagalog through.
     */
    private const ENGLISH_MARGIN = 0.05;

    private ?LanguageDetector $detector = null;

    /**
     * Determine whether the given text appears to be a Philippine language.
     */
    public function isPhilippineLanguage(?string $text): bool
    {
        $text = trim((string) $text);

        // Too short to judge reliably; never reject.
        if (mb_strlen($text) < 10) {
            return false;
        }

        try {
            $result = $this->detector()->detect($text);
        } catch (\Throwable $e) {
            // Never block a ticket because detection failed.
            return false;
        }

        $language = $result->language;

        if (!in_array($language, self::PHILIPPINE_LANGUAGES, true)) {
            return false;
        }

        $scores = $result->scores();
        $detectedScore = (float) ($scores[$language] ?? 0.0);
        $englishScore  = (float) ($scores['en'] ?? 0.0);

        return ($detectedScore - $englishScore) >= self::ENGLISH_MARGIN;
    }

    /**
     * Lazily build the detector so the (fairly large) language database is only
     * loaded when a field actually needs to be checked.
     */
    private function detector(): LanguageDetector
    {
        if ($this->detector === null) {
            $this->detector = new LanguageDetector(
                config('services.language_detection.database', 'large'),
                EldScheme::ISO639_1,
                EldMode::MODE_STRING
            );
        }

        return $this->detector;
    }
}
