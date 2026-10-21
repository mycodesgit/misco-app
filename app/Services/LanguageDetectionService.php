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

        // Only prose can be language-detected. Structured values such as email
        // addresses, asset tags, ticket references and serial numbers look like
        // prose to the detector but are not, and get misread as Malay/Indonesian
        // or Tagalog. Leave those to the other validation rules.
        if ($this->isStructuredValue($text)) {
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
     * Decide whether a value is structured data rather than natural language.
     *
     * Anything with an email address, a URL, or no word-like tokens at all
     * carries no reliable language signal, so it must not be judged by the
     * detector.
     */
    private function isStructuredValue(string $text): bool
    {
        // Email addresses (the common case in the password-reset form).
        if (filter_var($text, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }

        // A value that is mostly an email address or URL.
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', $text)) {
            return true;
        }
        if (preg_match('#https?://|www\.#i', $text)) {
            return true;
        }

        // Split on whitespace and keep only word-like tokens. A "word" needs at
        // least two consecutive letters; that filters out codes such as
        // "PRN-HP-0455" or "TKT-MIS-20261012-0001" and digits/symbols.
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordLike = 0;
        foreach ($words as $word) {
            if (preg_match('/[A-Za-z]{2,}/', $word)) {
                $wordLike++;
            }
        }

        // Fewer than two real words gives the detector nothing to work with.
        if ($wordLike < 2) {
            return true;
        }

        // Predominantly non-alphabetic content (codes, IDs, numbers).
        $letterCount = preg_match_all('/[A-Za-z]/', $text);
        $length      = max(mb_strlen($text), 1);
        if (($letterCount / $length) < 0.5) {
            return true;
        }

        return false;
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
