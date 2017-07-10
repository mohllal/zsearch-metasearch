<?php
// ***********
// STEMMING CLASSES
// ***********

/**
 * Implementation of the Porter Stemming algorithm.
 *
 * Usage:
 *  $stem = PorterStemmer::Stem($word);
 */
class PorterStemmer
{
    /**
     * Regex for matching a consonant
     * @var string
     */
    private static $regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

    /**
     * Regex for matching a vowel
     * @var string
     */
    private static $regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

    /**
     * Stems a word.
     * @param  string $word Word to stem
     * @return string       Stemmed word
     */
    public static function stem($word)
    {
        if (empty($word)) {
            return false;
        }

        $result = '';

        $word = strtolower($word);

        // Strip punctuation, etc. Keep ' and . for URLs and contractions.
        if (substr($word, -2) == "'s") {
            $word = substr($word, 0, -2);
        }
        $word = preg_replace("/[^a-z0-9'.-]/", '', $word);

        $first = '';
        if (strpos($word, '-') !== false) {
            $first = substr($word, 0, strrpos($word, '-') + 1); // Grabs hyphen too
            $word = substr($word, strrpos($word, '-') + 1);
        }

        if (strlen($word) > 2) {
            $word = self::step1ab($word);
            $word = self::step1c($word);
            $word = self::step2($word);
            $word = self::step3($word);
            $word = self::step4($word);
            $word = self::step5($word);
        }

        $result = $first . $word;

        return $result;
    }

    /**
     * Step 1ab
     *
     * @param string $word Word to stem
     * @return string stemmed word after applying step 1(a, b)
     */
    private static function step1ab($word)
    {
        // Part a
        if (substr($word, -1) == 's') {

            self::replace($word, 'sses', 'ss')
            OR self::replace($word, 'ies', 'i')
            OR self::replace($word, 'ss', 'ss')
            OR self::replace($word, 's', '');
        }

        // Part b
        if (substr($word, -2, 1) != 'e' OR !self::replace($word, 'eed', 'ee', 0)) { // First rule
            $v = self::$regex_vowel;

            // ing and ed
            if (preg_match("#$v+#", substr($word, 0, -3)) && self::replace($word, 'ing', '')
                OR preg_match("#$v+#", substr($word, 0, -2)) && self::replace($word, 'ed', '')
            ) { // Note use of && and OR, for precedence reasons

                // If one of above two test successful
                if (!self::replace($word, 'at', 'ate')
                    AND !self::replace($word, 'bl', 'ble')
                    AND !self::replace($word, 'iz', 'ize')
                ) {

                    // Double consonant ending
                    if (self::doubleConsonant($word)
                        AND substr($word, -2) != 'll'
                        AND substr($word, -2) != 'ss'
                        AND substr($word, -2) != 'zz'
                    ) {

                        $word = substr($word, 0, -1);

                    } else if (self::m($word) == 1 AND self::cvc($word)) {
                        $word .= 'e';
                    }
                }
            }
        }

        return $word;
    }

    /**
     * Step 1c
     *
     * @param string $word Word to stem
     * @return string stemmed word after applying step 1c
     */
    private static function step1c($word)
    {
        $v = self::$regex_vowel;

        if (substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1))) {
            self::replace($word, 'y', 'i');
        }

        return $word;
    }

    /**
     * Step 2
     *
     * @param string $word Word to stem
     * @return string stemmed word after applying step 2
     */
    private static function step2($word)
    {
        switch (substr($word, -2, 1)) {
            case 'a':
                self::replace($word, 'ational', 'ate', 0)
                OR self::replace($word, 'tional', 'tion', 0);
                break;

            case 'c':
                self::replace($word, 'enci', 'ence', 0)
                OR self::replace($word, 'anci', 'ance', 0);
                break;

            case 'e':
                self::replace($word, 'izer', 'ize', 0);
                break;

            case 'g':
                self::replace($word, 'logi', 'log', 0);
                break;

            case 'l':
                self::replace($word, 'entli', 'ent', 0)
                OR self::replace($word, 'ousli', 'ous', 0)
                OR self::replace($word, 'alli', 'al', 0)
                OR self::replace($word, 'bli', 'ble', 0)
                OR self::replace($word, 'eli', 'e', 0);
                break;

            case 'o':
                self::replace($word, 'ization', 'ize', 0)
                OR self::replace($word, 'ation', 'ate', 0)
                OR self::replace($word, 'ator', 'ate', 0);
                break;

            case 's':
                self::replace($word, 'iveness', 'ive', 0)
                OR self::replace($word, 'fulness', 'ful', 0)
                OR self::replace($word, 'ousness', 'ous', 0)
                OR self::replace($word, 'alism', 'al', 0);
                break;

            case 't':
                self::replace($word, 'biliti', 'ble', 0)
                OR self::replace($word, 'aliti', 'al', 0)
                OR self::replace($word, 'iviti', 'ive', 0);
                break;
        }

        return $word;
    }

    /**
     * Step 3
     *
     * @param string $word String to stem
     * @return string stemmed word after applying step 3
     */
    private static function step3($word)
    {
        switch (substr($word, -2, 1)) {
            case 'a':
                self::replace($word, 'ical', 'ic', 0);
                break;

            case 's':
                self::replace($word, 'ness', '', 0);
                break;

            case 't':
                self::replace($word, 'icate', 'ic', 0)
                OR self::replace($word, 'iciti', 'ic', 0);
                break;

            case 'u':
                self::replace($word, 'ful', '', 0);
                break;

            case 'v':
                self::replace($word, 'ative', '', 0);
                break;

            case 'z':
                self::replace($word, 'alize', 'al', 0);
                break;
        }

        return $word;
    }

    /**
     * Step 4
     *
     * @param string $word Word to stem
     * @return string stemmed word after applying step 4
     */
    private static function step4($word)
    {
        switch (substr($word, -2, 1)) {
            case 'a':
                self::replace($word, 'al', '', 1);
                break;

            case 'c':
                self::replace($word, 'ance', '', 1)
                OR self::replace($word, 'ence', '', 1);
                break;

            case 'e':
                self::replace($word, 'er', '', 1);
                break;

            case 'i':
                self::replace($word, 'ic', '', 1);
                break;

            case 'l':
                self::replace($word, 'able', '', 1)
                OR self::replace($word, 'ible', '', 1);
                break;

            case 'n':
                self::replace($word, 'ant', '', 1)
                OR self::replace($word, 'ement', '', 1)
                OR self::replace($word, 'ment', '', 1)
                OR self::replace($word, 'ent', '', 1);
                break;

            case 'o':
                if (substr($word, -4) == 'tion' OR substr($word, -4) == 'sion') {
                    self::replace($word, 'ion', '', 1);
                } else {
                    self::replace($word, 'ou', '', 1);
                }
                break;

            case 's':
                self::replace($word, 'ism', '', 1);
                break;

            case 't':
                self::replace($word, 'ate', '', 1)
                OR self::replace($word, 'iti', '', 1);
                break;

            case 'u':
                self::replace($word, 'ous', '', 1);
                break;

            case 'v':
                self::replace($word, 'ive', '', 1);
                break;

            case 'z':
                self::replace($word, 'ize', '', 1);
                break;
        }

        return $word;
    }

    /**
     * Step 5
     *
     * @param string $word Word to stem
     * @return string stemmed word after applying step 5
     */
    private static function step5($word)
    {
        // Part a
        if (substr($word, -1) == 'e') {
            if (self::m(substr($word, 0, -1)) > 1) {
                self::replace($word, 'e', '');

            } else if (self::m(substr($word, 0, -1)) == 1) {

                if (!self::cvc(substr($word, 0, -1))) {
                    self::replace($word, 'e', '');
                }
            }
        }

        // Part b
        if (self::m($word) > 1 AND self::doubleConsonant($word) AND substr($word, -1) == 'l') {
            $word = substr($word, 0, -1);
        }

        return $word;
    }

    /**
     * Replaces the first string with the second, at the end of the string. If third
     * arg is given, then the preceding string must match that m count at least.
     *
     * @param  string $str String to check
     * @param  string $check Ending to check for
     * @param  string $repl Replacement string
     * @param  int $m Optional minimum number of m() to meet
     * @return bool          Whether the $check string was at the end
     *                       of the $str string. True does not necessarily mean
     *                       that it was replaced.
     */
    private static function replace(&$str, $check, $repl, $m = null)
    {
        $len = 0 - strlen($check);

        if (substr($str, $len) == $check) {
            $substr = substr($str, 0, $len);
            if (is_null($m) OR self::m($substr) > $m) {
                $str = $substr . $repl;
            }

            return true;
        }

        return false;
    }

    /**
     * Measures the number of consonant sequences in $str. if c is
     * a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
     * presence,
     *
     * <c><v>       gives 0
     * <c>vc<v>     gives 1
     * <c>vcvc<v>   gives 2
     * <c>vcvcvc<v> gives 3
     *
     * @param  string $str The string to return the m count for
     * @return int         The m count
     */
    private static function m($str)
    {
        $c = self::$regex_consonant;
        $v = self::$regex_vowel;

        $str = preg_replace("#^$c+#", '', $str);
        $str = preg_replace("#$v+$#", '', $str);

        preg_match_all("#($v+$c+)#", $str, $matches);

        return count($matches[1]);
    }

    /**
     * Returns true/false as to whether the given string contains two
     * of the same consonant next to each other at the end of the string.
     *
     * @param  string $str String to check
     * @return bool        Result
     */
    private static function doubleConsonant($str)
    {
        $c = self::$regex_consonant;

        return preg_match("#$c{2}$#", $str, $matches) AND $matches[0]{0} == $matches[0]{1};
    }

    /**
     * Checks for ending CVC sequence where second C is not W, X or Y
     *
     * @param  string $str String to check
     * @return bool        Result
     */
    private static function cvc($str)
    {
        $c = self::$regex_consonant;
        $v = self::$regex_vowel;

        return preg_match("#($c$v$c)$#", $str, $matches)
            AND strlen($matches[1]) == 3
            AND $matches[1]{2} != 'w'
            AND $matches[1]{2} != 'x'
            AND $matches[1]{2} != 'y';
    }
}


/**
 * Implementation of the Porter2 Stemming Algorithm.
 *
 * Usage:
 *  $stem = Porter2::stem($word);
 */
class Porter2Stemmer
{

    /**
     * Computes the stem of the word.
     *
     * @param string $word word to be stemmed
     * @return string
     *   The word's stem.
     */
    public static function stem($word)
    {

        $exceptions = array(
            'skis' => 'ski', 'skies' => 'sky', 'dying' => 'die',
            'lying' => 'lie', 'tying' => 'tie', 'idly' => 'idl',
            'gently' => 'gentl', 'ugly' => 'ugli', 'early' => 'earli',
            'only' => 'onli', 'singly' => 'singl', 'sky' => 'sky',
            'news' => 'news', 'howe' => 'howe', 'atlas' => 'atlas',
            'cosmos' => 'cosmos', 'bias' => 'bias', 'andes' => 'andes',
        );

        // Process exceptions.
        if (isset($exceptions[$word])) {
            $word = $exceptions[$word];
        } elseif (strlen($word) > 2) {
            // Only execute algorithm on words that are longer than two letters.
            $word = self::prepare($word);
            $word = self::step0($word);
            $word = self::step1a($word);
            $word = self::step1b($word);
            $word = self::step1c($word);
            $word = self::step2($word);
            $word = self::step3($word);
            $word = self::step4($word);
            $word = self::step5($word);
        }
        return strtolower($word);
    }

    /**
     * Set initial y, or y after a vowel, to Y.
     *
     * @param string $word
     *   The word to be stemmed.
     *
     * @return string $word
     *   The prepared word.
     */
    private static function prepare($word)
    {
        $inc = 0;
        if (strpos($word, "'") === 0) {
            $word = substr($word, 1);
        }
        while ($inc <= strlen($word)) {
            if (substr($word, $inc, 1) === 'y' && ($inc == 0 || self::isVowel($inc - 1, $word))) {
                $word = substr_replace($word, 'Y', $inc, 1);
            }
            $inc++;
        }
        return $word;
    }

    /**
     * Search for the longest among the "s" suffixes and removes it.
     *
     * @param string $word
     *   The word to stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step0($word)
    {
        $found = FALSE;
        $checks = array("'s'", "'s", "'");
        foreach ($checks as $check) {
            if (!$found && self::hasEnding($word, $check)) {
                $word = self::removeEnding($word, $check);
                $found = TRUE;
            }
        }
        return $word;
    }

    /**
     * Handles various suffixes, of which the longest is replaced.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step1a($word)
    {
        $found = FALSE;
        if (self::hasEnding($word, 'sses')) {
            $word = self::removeEnding($word, 'sses') . 'ss';
            $found = TRUE;
        }
        $checks = array('ied', 'ies');
        foreach ($checks as $check) {
            if (!$found && self::hasEnding($word, $check)) {
                // @todo: check order here.
                $length = strlen($word);
                $word = self::removeEnding($word, $check);
                if ($length > 4) {
                    $word .= 'i';
                } else {
                    $word .= 'ie';
                }
                $found = TRUE;
            }
        }
        if (self::hasEnding($word, 'us') || self::hasEnding($word, 'ss')) {
            $found = TRUE;
        }
        // Delete if preceding word part has a vowel not immediately before the s.
        if (!$found && self::hasEnding($word, 's') && self::containsVowel(substr($word, 0, -2))) {
            $word = self::removeEnding($word, 's');
        }
        return $word;
    }

    /**
     * Handles various suffixes, of which the longest is replaced.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step1b($word)
    {
        $exceptions = array(
            'inning', 'outing', 'canning',
            'herring', 'earring', 'proceed', 'exceed', 'succeed',
        );
        if (in_array($word, $exceptions)) {
            return $word;
        }
        $checks = array('eedly', 'eed');
        foreach ($checks as $check) {
            if (self::hasEnding($word, $check)) {
                if (self::r($word, 1) !== strlen($word)) {
                    $word = self::removeEnding($word, $check) . 'ee';
                }
                return $word;
            }
        }
        $checks = array('ingly', 'edly', 'ing', 'ed');
        $second_endings = array('at', 'bl', 'iz');
        foreach ($checks as $check) {
            // If the ending is present and the previous part contains a vowel.
            if (self::hasEnding($word, $check) && self::containsVowel(substr($word, 0, -strlen($check)))) {
                $word = self::removeEnding($word, $check);
                foreach ($second_endings as $ending) {
                    if (self::hasEnding($word, $ending)) {
                        return $word . 'e';
                    }
                }
                // If the word ends with a double, remove the last letter.
                $double_removed = self::removeDoubles($word);
                if ($double_removed != $word) {
                    $word = $double_removed;
                } elseif (self::isShort($word)) {
                    // If the word is short, add e (so hop -> hope).
                    $word .= 'e';
                }
                return $word;
            }
        }
        return $word;
    }

    /**
     * Replaces suffix y or Y with i if after non-vowel not @ word begin.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step1c($word)
    {
        if ((self::hasEnding($word, 'y') || self::hasEnding($word, 'Y')) && strlen($word) > 2 && !(self::isVowel(strlen($word) - 2, $word))) {
            $word = self::removeEnding($word, 'y');
            $word .= 'i';
        }
        return $word;
    }

    /**
     * Implements step 2 of the Porter2 algorithm.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step2($word)
    {
        $checks = array(
            "ization" => "ize", "iveness" => "ive", "fulness" => "ful",
            "ational" => "ate", "ousness" => "ous", "biliti" => "ble",
            "tional" => "tion", "lessli" => "less", "fulli" => "ful",
            "entli" => "ent", "ation" => "ate", "aliti" => "al",
            "iviti" => "ive", "ousli" => "ous", "alism" => "al",
            "abli" => "able", "anci" => "ance", "alli" => "al",
            "izer" => "ize", "enci" => "ence", "ator" => "ate",
            "bli" => "ble", "ogi" => "og",
        );
        foreach ($checks as $find => $replace) {
            if (self::hasEnding($word, $find)) {
                if (self::inR1($word, $find)) {
                    $word = self::removeEnding($word, $find) . $replace;
                }
                return $word;
            }
        }
        if (self::hasEnding($word, 'li')) {
            if (strlen($word) > 4 && self::validLi(self::charAt(-3, $word))) {
                $word = self::removeEnding($word, 'li');
            }
        }
        return $word;
    }

    /**
     * Implements step 3 of the Porter2 algorithm.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step3($word)
    {
        $checks = array(
            'ational' => 'ate', 'tional' => 'tion', 'alize' => 'al',
            'icate' => 'ic', 'iciti' => 'ic', 'ical' => 'ic',
            'ness' => '', 'ful' => '',
        );
        foreach ($checks as $find => $replace) {
            if (self::hasEnding($word, $find)) {
                if (self::inR1($word, $find)) {
                    $word = self::removeEnding($word, $find) . $replace;
                }
                return $word;
            }
        }
        if (self::hasEnding($word, 'ative')) {
            if (self::inR2($word, 'ative')) {
                $word = self::removeEnding($word, 'ative');
            }
        }
        return $word;
    }

    /**
     * Implements step 4 of the Porter2 algorithm.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step4($word)
    {
        $checks = array(
            'ement', 'ment', 'ance', 'ence', 'able',
            'ible', 'ant', 'ent', 'ion', 'ism',
            'ate', 'iti', 'ous', 'ive', 'ize',
            'al', 'er', 'ic',
        );

        foreach ($checks as $check) {
            // Among the suffixes, if found and in R2, delete.
            if (self::hasEnding($word, $check)) {
                if (self::inR2($word, $check)) {
                    if ($check !== 'ion' || in_array(self::charAt(-4, $word), array('s', 't'))) {
                        $word = self::removeEnding($word, $check);
                    }
                }
                return $word;
            }
        }
        return $word;
    }

    /**
     * Implements step 5 of the Porter2 algorithm.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function step5($word)
    {
        if (self::hasEnding($word, 'e')) {
            // Delete if in R2, or in R1 and not preceded by a short syllable.
            if (self::inR2($word, 'e') || (self::inR1($word, 'e') && !self::isShortSyllable($word, strlen($word) - 3))) {
                $word = self::removeEnding($word, 'e');
            }
            return $word;
        }
        if (self::hasEnding($word, 'l')) {
            // Delete if in R2 and preceded by l.
            if (self::inR2($word, 'l') && self::charAt(-2, $word) == 'l') {
                $word = self::removeEnding($word, 'l');
            }
        }
        return $word;
    }

    /**
     * Removes certain double consonants from the word's end.
     *
     * @param string $word
     *   The word to be stemmed
     *
     * @return string $word
     *   The modified word.
     */
    private static function removeDoubles($word)
    {
        $doubles = array('bb', 'dd', 'ff', 'gg', 'mm', 'nn', 'pp', 'rr', 'tt');
        foreach ($doubles as $double) {
            if (substr($word, -2) == $double) {
                $word = substr($word, 0, -1);
                break;
            }
        }
        return $word;
    }

    /**
     * Checks whether a character is a vowel.
     *
     * @param int $position
     *   The character's position.
     * @param string $word
     *   The word in which to check.
     * @param string[] $additional
     *   (optional) Additional characters that should count as vowels.
     *
     * @return bool
     *   TRUE if the character is a vowel, FALSE otherwise.
     */
    private static function isVowel($position, $word, array $additional = array())
    {
        $vowels = array_merge(array('a', 'e', 'i', 'o', 'u', 'y'), $additional);
        return in_array(self::charAt($position, $word), $vowels);
    }

    /**
     * Retrieves the character at the given position.
     *
     * @param int $position
     *   The 0-based index of the character. If a negative number is given, the
     *   position is counted from the end of the string.
     * @param string $word
     *   The word from which to retrieve the character.
     *
     * @return string
     *   The character at the given position, or an empty string if the given
     *   position was illegal.
     */
    private static function charAt($position, $word)
    {
        $length = strlen($word);
        if (abs($position) >= $length) {
            return '';
        }
        if ($position < 0) {
            $position += $length;
        }
        return $word[$position];
    }

    /**
     * Determines whether the word ends in a "vowel-consonant" suffix.
     *
     * Unless the word is only two characters long, it also checks that the
     * third-last character is neither "w", "x" nor "Y".
     *
     * @param string $word
     *   The word in which to check.
     * @param int|null $position
     *   (optional) If given, do not check the end of the word, but the character
     *   at the given position, and the next one.
     *
     * @return bool
     *   TRUE if the word has the described suffix, FALSE otherwise.
     */
    private static function isShortSyllable($word, $position = NULL)
    {
        if ($position === NULL) {
            $position = strlen($word) - 2;
        }
        // A vowel at the beginning of the word followed by a non-vowel.
        if ($position === 0) {
            return self::isVowel(0, $word) && !self::isVowel(1, $word);
        }
        // Vowel followed by non-vowel other than w, x, Y and preceded by
        // non-vowel.
        $additional = array('w', 'x', 'Y');
        return !self::isVowel($position - 1, $word) && self::isVowel($position, $word) && !self::isVowel($position + 1, $word, $additional);
    }

    /**
     * Determines whether the word is short.
     * A word is called short if it ends in a short syllable and if R1 is null.
     *
     * @param string $word
     *   The word in which to check.
     * @return bool
     *   TRUE if the word is short, FALSE otherwise.
     */
    private static function isShort($word)
    {
        return self::isShortSyllable($word) && self::r($word, 1) == strlen($word);
    }

    /**
     * Determines the start of a certain "R" region.
     *
     * R is a region after the first non-vowel following a vowel, or end of word.
     *
     * @param string $word
     *   The word in which to check.
     * @param int $type
     *   (optional) 1 or 2. If 2, then calculate the R after the R1.
     *
     * @return int
     *   The R position.
     */
    private static function r($word, $type = 1)
    {
        $inc = 1;
        if ($type === 2) {
            $inc = self::r($word, 1);
        } elseif (strlen($word) > 5) {
            $prefix_5 = substr($word, 0, 5);
            if ($prefix_5 === 'gener' || $prefix_5 === 'arsen') {
                return 5;
            }
            if (strlen($word) > 5 && substr($word, 0, 6) === 'commun') {
                return 6;
            }
        }
        while ($inc <= strlen($word)) {
            if (!self::isVowel($inc, $word) && self::isVowel($inc - 1, $word)) {
                $position = $inc;
                break;
            }
            $inc++;
        }
        if (!isset($position)) {
            $position = strlen($word);
        } else {
            // We add one, as this is the position AFTER the first non-vowel.
            $position++;
        }
        return $position;
    }

    /**
     * Checks whether the given string is contained in R1.
     *
     * @param string $word
     *   The word in which to check.
     * @param string $string
     *   The string.
     *
     * @return bool
     *   TRUE if the string is in R1, FALSE otherwise.
     */
    private static function inR1($word, $string)
    {
        $r1 = substr($word, self::r($word, 1));
        return strpos($r1, $string) !== FALSE;
    }

    /**
     * Checks whether the given string is contained in R2.
     *
     * @param string $word
     *   The word in which to check.
     * @param string $string
     *   The string.
     *
     * @return bool
     *   TRUE if the string is in R2, FALSE otherwise.
     */
    private static function inR2($word, $string)
    {
        $r2 = substr($word, self::r($word, 2));
        return strpos($r2, $string) !== FALSE;
    }

    /**
     * Checks whether the word ends with the given string.
     *
     * @param string $word
     *   The word in which to check.
     * @param string $string
     *   The string.
     *
     * @return bool
     *   TRUE if the word ends with the given string, FALSE otherwise.
     */
    private static function hasEnding($word, $string)
    {
        $length = strlen($string);
        if ($length > strlen($word)) {
            return FALSE;
        }
        return (substr_compare($word, $string, -1 * $length, $length) === 0);
    }

    /**
     * Removes a given string from the end of the current word.
     *
     * Does not check whether the ending is actually there.
     *
     * @param string $word
     *   The word in which to check.
     * @param string $string
     *   The ending to remove.
     * @return string $word after removing ending
     */
    private static function removeEnding($word, $string)
    {
        return substr($word, 0, -strlen($string));
    }

    /**
     * Checks whether the given string contains a vowel.
     *
     * @param string $string
     *   The string to check.
     *
     * @return bool
     *   TRUE if the string contains a vowel, FALSE otherwise.
     */
    private static function containsVowel($string)
    {
        $inc = 0;
        $return = FALSE;
        while ($inc < strlen($string)) {
            if (self::isVowel($inc, $string)) {
                $return = TRUE;
                break;
            }
            $inc++;
        }
        return $return;
    }

    /**
     * Checks whether the given string is a valid -li prefix.
     *
     * @param string $string
     *   The string to check.
     *
     * @return bool
     *   TRUE if the given string is a valid -li prefix, FALSE otherwise.
     */
    private static function validLi($string)
    {
        return in_array($string, array(
            'c', 'd', 'e', 'g', 'h', 'k', 'm', 'n', 'r', 't',
        ));
    }
}

// ***********
// QUERY CLASS
// ***********

// Purpose: to handle all query processing

class query
{
    // **********
    // PROPERTIES
    // **********
    private $queryTokens = array();
    private $suggestions = array();

    // **********
    // METHODS
    // **********
    // Tokenize query
    public function tokenizeQuery($q){

        $this->queryTokens = explode(" ",$q);
        return $this->queryTokens;
    }

    // Display tokens
    public function displayTokens(){

        foreach($this->queryTokens as $item)
            echo '<br>'.$item;
    }

    // Google complex queries
    public function complexQueryGoogle($q){

        $q=str_replace(" NOT "," -",$q);
        $q=urlencode("'$q'");
        return $q;
    }

    // Bing complex queries
    public function complexQueryBing($q){

        $q=urlencode("'$q'");
        return $q;
    }

    // Expand query
    public function expandQuery($q, $thesaurus){

        foreach ($this->queryTokens as $keyQ=>$valueQ)
        {
            foreach($thesaurus as $keyT=>$valueT)
                if($valueQ == $keyT)
                {
                    //Appends words with the same meaning after  q+ and +->{other meanings}
                    $q .= ' AND '.$valueT;
                }
        }
        $q = str_replace(",", " OR ", $q);
        echo '<strong>> Expansion:</strong> '.$q.'<br>';
        return $q;
    }

    public function makeSuggestions($q,$thesaurus){
        $query_suggestions=array();
        $arr=array();
        $arr[]=false;
        $jj=0;
        $this->suggestions[]=array();
        foreach ($this->queryTokens as $keyQ=>$valueQ)
        {
            $term_eq=array();
            foreach($thesaurus as $keyT=>$valueT){
                if($valueQ == $keyT)
                {
                    $term_eq=explode(',', $valueT);
                    $this->suggestions[$jj]=$term_eq;
                    $arr[$jj]=true;
                    break;
                }
            }
            $jj+=1;

        }

        $countTokens=count($this->queryTokens);
        $countSugs=count($this->suggestions);
        for($i=0;$i<$countTokens;$i++){
            if($i>$countSugs - 1)
                break;
            if(count($this->suggestions[$i])==0){
                continue;
            }

            $arr2=$this->suggestions[$i];

            $ii=0;
            foreach ($arr2 as $key => $value) {
                if($ii==3)
                    break;
                $str=str_replace($this->queryTokens[$i], $value, $q);
                $query_suggestions[]=$str;
                $ii++;
            }
        }

        echo "<br><strong> Suggestions: </strong>".'<br>';

        $i=0;
        foreach ($query_suggestions as $key => $value) {
            if($i++!=0){
                echo ' , ';
            }

            echo' <a href =search.php?q='.urlencode($value).'&result_op='.$_SESSION['result_op'].'>'.$value.'</a>';
        }
        echo "<br>";
    }

    // ******************
    // Stemming
    // ******************
    /**
     *  Takes a list of words and returns them reduced to their stems.
     *
     *  $words can be either a string or an array. If it is a string, it will
     *  be split into separate words on whitespace, commas, or semicolons. If
     *  an array, it assumes one word per element.
     *
     * @param mixed $words String or array of word(s) to reduce
     * @param string $stemmerAlgo stemming algorithm to be applied
     * @access public
     * @return array List of word stems
     */
    function stem_list($words, $stemmerAlgo)
    {
        if (empty($words)) {
            return false;
        }

        $results = array();

        if (!is_array($words)) {
            $words = preg_split("/[\s,]+/", trim($words));
        }

        $numItems = count($words);
        $currentIndex = 0;
        $output = '';
        foreach ($words as $word) {
            $result = $word;
            if ($stemmerAlgo == 'porter' && $result = PorterStemmer::stem($word))
                $results[] = $result;
            else if ($stemmerAlgo == 'porter2' && $result = Porter2Stemmer::stem($word))
                $results[] = $result;

            $output .= $word . '->' . $result;

            if(++$currentIndex !== $numItems)
                $output .= ' , ';
        }
        echo '<strong> Stemming:</strong><br> ' . $output . '<br>';
        return $results;
    }
}


// **********
// API CLASS
// **********

// Purpose: To handle all interaction with external APIs

class api
{
    // **********
    // PROPERTIES
    // **********

    // Gooogle JSON data and flags for checking usable data is present
    private $js1;
    private $js1ResultFlag;
    // Bing JSON var
    private $js2;
    private $js2ResultFlag;

    // **********
    // METHODS
    // **********
    // Display Google JSON
    public function displayGoogleJsonData() {
        echo '<br><strong>Vardump:</strong><br><br>';
        var_dump($this->js1);
        echo '<br><br>';
    }

    // Return Google JSON
    public function returnGoogleJsonData() {
        return $this->js1;
    }

    // Return Google JSON Results Flag
    public function returnGoogleJsonResultFlag() {
        return $this->js1ResultFlag;
    }

    // Display Bing JSON
    public function displayBingJsonData() {
        echo '<br><strong>Vardump:</strong><br><br>';
        var_dump($this->js2);
        echo '<br><br>';
    }

    // Return Bing JSON
    public function returnBingJsonData() {
        return $this->js2;
    }

    // Return Bing JSON Results Flag
    public function returnBingJsonResultFlag() {
        return $this->js2ResultFlag;
    }

    // **********
    // Google API
    // **********
    public function googleApi($q, $offset) {
        // Multiple keys available due to overcome limits of 100 queries per day per key
        //var_dump($offset);
        // Google API key 1
        //$googleApiKey='AIzaSyB-cD7zquUll6JEh70loVHd3GaBTSIcgKQ';
        // Google API key 2
        //$googleApiKey='AIzaSyDimuSeHydkOg1pyyO54Q3EVEGjPRdUfKM';
        // Google API key 3
        //$googleApiKey='AIzaSyC-1kR2M8m2iJhnAXTwZXp8w3gks8x5YOA';
        // Google API key 4
        //$googleApiKey='AIzaSyB8nKZMt6s6I3tDioe0lhJKBFX479Bj7-c';
        // Google API key 5
        //$googleApiKey='AIzaSyAvA447TaB5lFH_dkohICwoJEGvXYa0Mao';
        // Google API key 6
        $googleApiKey='AIzaSyCBpYjocDnbupnAxbK6XqN5D-mrohReaIU';
        $id='005243230011887027180:95rpocypqje';
        // Construct the link
        $url='https://www.googleapis.com/customsearch/v1?'.'key='.$googleApiKey.'&cx='.$id.'&q='.$q.'&alt=json'.'&start='.$offset.((isset($_SESSION['type']) && $_SESSION['type']=='image')?'&searchType=image':'');
        //var_dump($url);
        // Clean spaces from the string
        $url=str_replace(' ','%20',$url);
        // initiate cURL
        $ch=curl_init();
        // set the URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Fix the SSL cery problem with Google custom search API
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // get the page source into $data variable
        $data = curl_exec($ch);
        //echo '<br>Vardumpdata3:';
        //var_dump($data);
        // json decode
        $this->js1 = json_decode($data);
        //echo '<br><br>Vardumpjs1: ('.$offset.')<br><br>';
        //var_dump($this->js1);
        // Echo total results from Google
        //echo '<br>Google Results: '.$this->js1->{'searchInformation'}->{'totalResults'};
        // Set results Flag
        if (!empty($this->js1->{'searchInformation'}->{'totalResults'}) > '0')
        {
            //echo '<br>Blekko API: Results!!!';
            $this->js1ResultFlag = TRUE;
        }
        else
        {
            //echo '<br>Blekko API: No Results!';
            $this->js1ResultFlag = FALSE;
        }
        // Stores data in a file if required
        //$content = serialize($this->js1);
        //file_put_contents('dirname(__FILE__).'/json/tmp1',$content);
        /*
        */
        // Recovers data from a file if required
        //$this->js1 = unserialize(file_get_contents('dirname(__FILE__).'/json/'tmp1'));
        //$this->js1ResultFlag = TRUE;
    }

    // **********
    // Bing API
    // **********
    public function bingApi($q, $results, $offset) {

        //var_dump($offset);

        // Keys
        $acctKey = 'b173d7c872b242d6a63e2ab0d4dd3891';
        $rootUri = 'https://api.cognitive.microsoft.com/bing/v5.0/'.((isset($_SESSION['type']) && $_SESSION['type']=='image')?$_SESSION['type'].'s/':'').'search';
        //var_dump($rootUri);

        // Construct the full URI for the query.
        $requestUri = $rootUri.'?q='.$q.'&offset='.$offset;
        // construct the key header array
        $headers = array('Ocp-Apim-Subscription-Key : '.$acctKey);

        //
        $ch = curl_init($requestUri);

        curl_setopt($ch, CURLOPT_URL, $requestUri);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        // responses decode
        $this->js2 = json_decode($data);
        //echo '<br><br>Vardumpjs2: ('.$offset.')<br><br>';
        //var_dump($this->js2->{'totalEstimatedMatches'});

        // Set results Flag
        $this->js2ResultFlag = (!empty($this->js2->{'webPages'}->{'totalEstimatedMatches'}) > '0' || !empty($this->js2->{'totalEstimatedMatches'}) > '0' )?TRUE:FALSE;

        // Stores data in a file if required
        //$content = serialize($this->js2);
        //file_put_contents('dirname(__FILE__).'/responses/bing',$content);
        /*
        */
        // Recovers data from a file if required
        //$this->js2 = unserialize(file_get_contents('dirname(__FILE__).'/responses/bing'));
        //$this->js2ResultFlag = TRUE;

    } // End of Bing API Class

} // End of API Class


// **********
// RESULT SET CLASS
// **********

// Purpose: To handle all the result sets from each input search engine in a single object

class resultSet
{
    // **********
    // PROPERTIES
    // **********

    private $numItems = 0;
    private $urls = array();
    private $titles = array();
    private $snippets = array();
    private $scores = array();


    // **********
    // METHODS
    // **********
    // Add Url to resultSet
    public function addUrl($url){
        array_push($this->urls, $url);
        $this->addNumItems();
    }

    // Unset Url from Agg liist - for displaying clusters
    public function unsetUrl($url){
        unset($this->urls[$url]);
        $this->removeNumItems();
    }

    // Add Title
    public function addTitle($title){
        array_push($this->titles, $title);
    }

    // Unset Title
    public function unsetTitle($title){
        unset($this->titles[$title]);
    }

    // Add Snippet
    public function addSnippet($snippet){
        array_push($this->snippets, $snippet);
    }

    // Unset Snippet
    public function unsetSnippet($snippet){
        unset($this->snippets[$snippet]);
    }

    // Add Score
    public function addScore($score){
        array_push($this->scores, $score);
    }

    // Unset Score
    public function unsetScore($score){
        unset($this->scores[$score]);
    }

    // Increment num items
    public function addNumItems(){
        $this->numItems++;
    }

    // Decrement num items
    public function removeNumItems(){
        $this->numItems--;
    }

    // Print Urls
    public function printUrls(){
        foreach($this->urls as $item)
            echo $item.'<br>';
    }

    // Print Titles
    public function printTitles(){
        foreach($this->titles as $item)
            echo $item.'<br>';
    }

    // Print Snippets
    public function printSnippets(){
        foreach($this->snippets as $item)
            echo $item.'<br>';
    }

    // Print Scores
    public function printScores(){
        foreach($this->scores as $item)
            echo $item.'<br>';
    }

    // RETURN URLS
    public function returnUrls(){
        return $this->urls;
    }

    // RETURN URLS V2
    public function returnUrlsV2($i){
        return $this->urls[$i];
    }

    public function returnUrlswithoutwww($i){
        return $this->urls_without_www[$i];
    }

    // RETURN TITLES
    public function returnTitles(){
        return $this->titles;
    }

    // RETURN TITLES V2
    public function returnTitlesV2($i){
        return $this->titles[$i];
    }

    // RETURN SNIPPETS
    public function returnSnippets(){
        return $this->snippets;
    }

    // RETURN SNIPPETS V2
    public function returnSnippetsV2($i){
        return $this->snippets[$i];
    }

    // RETURN SCORES
    public function returnScores(){
        return $this->scores;
    }

    // RETURN SCORES V2
    public function returnScoresV2($i){
        return $this->scores[$i];
    }

    // RETURN Num Items
    public function returnNumItems(){
        return $this->numItems;
    }

    // SUM Fused Scores
    public function sumFusedScores($fusedScore, $index, $weight){
        $this->scores[$index]+=($fusedScore*$weight);
    }

} // End of resultSet Class


// **********
// FORMATTER CLASS
// **********
// Purpose: To format the JSON data returned from the input search engines and make available to other objects in the program 

class formatter
{
    // **********
    // PROPERTIES
    // **********

    // Google Formatted ResultSet Properties
    private $resultSet1 = NULL;
    private $js1;
    private $js1ResultFlag;

    // Bing Properties
    private $resultSet2 = NULL;
    private $js2;
    private $js2ResultFlag;

    // Ask Properties
    private $resultSet3 = NULL;

    // **********
    // METHODS
    // **********
    // Object Property Constructor
    public function __construct(resultSet $resultSet1, resultSet $resultSet2, resultSet $resultSet3) {
        $this->resultSet1 = $resultSet1;
        $this->resultSet2 = $resultSet2;
        $this->resultSet3 = $resultSet3;
    }

    // Set Google JSON data
    public function setGoogleJson($js_import, $js1ResultFlag) {
        $this->js1 = $js_import;
        $this->js1ResultFlag = $js1ResultFlag;
    }

    // Render GOOGLE data from JSON object to result set property
    public function formatGoogleJson($results, $offset, $query) {

        if($this->js1ResultFlag == TRUE)
        {
            // Rank starting from 1
            // Score starting from $_SESSION['results'] and down - Borda Count

            $j = $results-$offset + 1;
            foreach($this->js1->{'items'} as $item)
            {
                if(!in_array($this->cleanLink($item->{'link'}), $this->resultSet1->returnUrls(), TRUE))
                {
                    $item_link = $this->cleanLink($item->{'link'});
                    $item_title = $this->cleanText($item->{'title'});
                    $item_snippet = $this->cleanText($item->{'snippet'});
                    $item_score = $j;

                    $this->resultSet1->addUrl($item_link);
                    $this->resultSet1->addTitle($item_title);
                    $this->resultSet1->addSnippet($item_snippet);
                    $this->resultSet1->addScore($item_score);

                    $j--;

                    $conn = new mysqli("localhost", "root", "", "z-search");
                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $item_query = $conn->real_escape_string($query);
                    $item_link = $conn->real_escape_string($item_link);
                    $item_title = $conn->real_escape_string($item_title);
                    $item_snippet = $conn->real_escape_string($item_snippet);
                    $item_source = 'Google';
                    $item_type = (isset($_SESSION['type']) && $_SESSION['type']=='image')? 'image' : 'text';

                    $sql_query = "INSERT IGNORE INTO results (result_keywords, result_source, result_type, result_offset, result_score, result_title, result_link, result_snippet)
VALUES ('$item_query', '$item_source', '$item_type', '$offset', '$item_score', '$item_title', '$item_link', '$item_snippet')";

                    if ($conn->query($sql_query) === FALSE) {
                        echo "Error: " . $sql_query . "<br>" . $conn->error;
                    }
                    $conn->close();
                }
            }
        }
        else{
            ; //echo '<br>Google: No Results!!!';
        }
    }

    // Set BING JSON data
    public function setBingJson($js_import, $js2ResultFlag) {
        $this->js2 = $js_import;
        $this->js2ResultFlag = $js2ResultFlag;
    }

    // Render BING data from JSON object to result set property
    public function formatBingJson($results, $offset, $query) {

        if($this->js2ResultFlag == TRUE){

            $j = $results - $offset + 1;
            if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
                foreach($this->js2->{'value'} as $item)
                {
                    if(!in_array($this->cleanLink($item->{'contentUrl'}), $this->resultSet2->returnUrls(), TRUE))
                    {
                        $item_link = $this->cleanLink($item->{'contentUrl'});
                        $item_title = $this->cleanText($item->{'name'});
                        $item_snippet = $this->cleanText($item->{'name'});
                        $item_score = $j;

                        $this->resultSet2->addUrl($item_link);
                        $this->resultSet2->addTitle($item_title);
                        $this->resultSet2->addSnippet($item_snippet);
                        $this->resultSet2->addScore($item_score);
                        $j--;

                        $conn = new mysqli("localhost", "root", "", "z-search");
                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        $item_query = $conn->real_escape_string($query);
                        $item_link = $conn->real_escape_string($item_link);
                        $item_title = $conn->real_escape_string($item_title);
                        $item_snippet = $conn->real_escape_string($item_snippet);
                        $item_source = 'Bing';
                        $item_type = 'image';

                        $sql_query = "INSERT IGNORE INTO results (result_keywords, result_source, result_type, result_offset, result_score, result_title, result_link, result_snippet)
VALUES ('$item_query', '$item_source', '$item_type', '$offset', '$item_score', '$item_title', '$item_link', '$item_snippet')";

                        if ($conn->query($sql_query) === FALSE) {
                            echo "Error: " . $sql_query . "<br>" . $conn->error;
                        }
                        $conn->close();
                    }
                }
            }else{
                foreach($this->js2->{'webPages'}->{'value'} as $item)
                {
                    if(!in_array($this->cleanLink($item->{'displayUrl'}), $this->resultSet2->returnUrls(), TRUE))
                    {
                        $item_link = $this->cleanLink($item->{'displayUrl'});
                        $item_title = $this->cleanText($item->{'name'});
                        $item_snippet = $this->cleanText($item->{'snippet'});
                        $item_score = $j;

                        $this->resultSet2->addUrl($item_link);
                        $this->resultSet2->addTitle($item_title);
                        $this->resultSet2->addSnippet($item_snippet);
                        $this->resultSet2->addScore($item_score);
                        $j--;

                        $conn = new mysqli("localhost", "root", "", "z-search");
                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        $item_query = $conn->real_escape_string($query);
                        $item_link = $conn->real_escape_string($item_link);
                        $item_title = $conn->real_escape_string($item_title);
                        $item_snippet = $conn->real_escape_string($item_snippet);
                        $item_source = 'Bing';
                        $item_type = 'text';

                        $sql_query = "INSERT IGNORE INTO results (result_keywords, result_source, result_type, result_offset, result_score, result_title, result_link, result_snippet)
VALUES ('$item_query', '$item_source', '$item_type', '$offset', '$item_score', '$item_title', '$item_link', '$item_snippet')";

                        if ($conn->query($sql_query) === FALSE) {
                            echo "Error: " . $sql_query . "<br>" . $conn->error;
                        }
                        $conn->close();
                    }
                }
            }
        }
        else{
            ; // echo '<br>Bing: No Results!!!';
        }
    }

    // Render ASK data to result set property
    public function formatAskResults($askResults, $query, $offset){
        $resultsCount=count($askResults);

        //echo "COUNT ASK :: ".$resultsCount;

        if($resultsCount>0){
            $resultMaxScore = 100;
            foreach ($askResults as $key => $value) {
                $item_link = $this->cleanLink($value->getLink());
                $item_title = $this->cleanText($value->getTitle());
                $item_snippet = $this->cleanText(!empty($value->getAbstract()) ? $value->getAbstract() : '');
                $item_score = $resultMaxScore-$value->getScore() + 1;

                $this->resultSet3->addUrl($item_link);
                $this->resultSet3->addTitle($item_title);
                $this->resultSet3->addSnippet($item_snippet);
                $this->resultSet3->addScore($item_score);

                $conn = new mysqli("localhost", "root", "", "z-search");
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $item_query = $conn->real_escape_string($query);
                $item_link = $conn->real_escape_string($item_link);
                $item_title = $conn->real_escape_string($item_title);
                $item_snippet = $conn->real_escape_string($item_snippet);
                $item_source = 'Ask';
                $item_type = 'text';

                $sql_query = "INSERT IGNORE INTO results (result_keywords, result_source, result_type, result_offset, result_score, result_title, result_link, result_snippet)
VALUES ('$item_query', '$item_source', '$item_type', '$offset' ,'$item_score', '$item_title', '$item_link', '$item_snippet')";

                if ($conn->query($sql_query) === FALSE) {
                    echo "Error: " . $sql_query . "<br>" . $conn->error;
                }
                $conn->close();
            }
        }
    }

    // Print Urls
    public function printUrls($resultSet) {
        $this->$resultSet->printUrls();
    }

    // Print Titles
    public function printTitles($resultSet) {
        $this->$resultSet->printTitles();
    }

    // Print Snippets
    public function printSnippets($resultSet) {
        $this->$resultSet->printSnippets();
    }

    // Print Scores
    public function printScores($resultSet) {
        $this->$resultSet->printScores();
    }

    // Return Resultset
    public function returnResultSet($resultSet) {
        return $this->$resultSet;
    }

    // Clean Link
    public function cleanLink($link) {

        $link = strip_tags($link);
        //removes / if exists at the end of the link
        if (substr($link,-1,1) == '/') $link = substr($link,0,strlen($link) - 1);

        if (strpos($link, 'http') === false) {
            $link = "http://".$link;
        }
        return $link;
    }

    // Clean Text
    public function cleanText($text) {

        $text = strip_tags($text);
        return $text;
    }

    // Print Result Set
    // Iterate the sorted key array
    public function printResultSet($resultSet, $int) {

        $i=0;
        if($this->$resultSet->returnUrls() != NULL){
            if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
                foreach($this->$resultSet->returnUrls() as $key=>$value)
                {
                    // Print the details according to the ordered array
                    echo '<br>'.'<a href="'.$this->$resultSet->returnUrlsV2($i).'"><img class="img-rounded" src="'.$this->$resultSet->returnUrlsV2($i).'" alt="'.$this->$resultSet->returnTitlesV2($i).'"></img></a>';
                    $i++;
                }
            }  else {
                foreach($this->$resultSet->returnUrls() as $key=>$value)
                {
                    // Print the details according to the ordered array
                    echo '<br>'.'<a href="'.$this->$resultSet->returnUrlsV2($i).'">'.'<strong>#'.($i+$_SESSION['offset']).': '.$this->$resultSet->returnTitlesV2($i).'</strong>'.'</a>';
                    echo '<br>'.'<a href="'.$this->$resultSet->returnUrlsV2($i).'">'.$this->$resultSet->returnUrlsV2($i).'</a>';
                    echo '<br>'.$this->$resultSet->returnSnippetsV2($i);
                    echo '<br>Score: '.$this->$resultSet->returnScoresV2($i);
                    $i++;
                }
            }
        }
        else {
            echo '<br>Sorry, no results available!';
        }
    }

    // Output Result Set to File
    // Iterate the sorted key array
    public function outputResultSetToFile($filename, $resultSet, $int) {

        $target = $filename;
        $th=fopen(dirname(__FILE__).'/metrics/'.$target, 'a')or die("Couldn't open file, sorry");
        if($this->$resultSet->returnUrls() == NULL)
            echo '<br>No results!!!';
        else {
            for($i=0; $i<$int;$i++)
            {
                // Print the details according to the ordered array
                {
                    $line = ''.$this->$resultSet->returnUrlsV2($i).PHP_EOL;
                    fwrite($th, $line);
                }
            }
        }
        fclose($th);
    }

} // End of Formatter Class


// **********
// AGGREGATOR CLASS
// **********

// Purpose: To aggregate the search results and display them as required
class aggregator
{
    // **********
    // PROPERTIES
    // **********

    private $resultSetAgg = NULL;
    private $resultSetAggCluster = NULL;

    // **********
    // METHODS
    // **********

    // Object Property Constructor
    public function __construct(resultSet $resultSetAgg) {
        $this->resultSetAgg = $resultSetAgg;
    }

    // ***********************
    // Aggregation Algorithm
    // ***********************

    // Weighted Borda-Fuse
    public function dataFusion($resultSetFlag1, $resultSetFlag2, $resultSetFlag3, $resultSet1, $resultSet2, $resultSet3,$resNums) {

        // input search engine weights
        $weight1=1.34;
        $weight2=1.27;
        $weight3=1.13;

        // Conditional Initialising of the the aggregated array - includes checks for error of null result set
        if ($resultSetFlag2 == TRUE)
        {
            $count = $resNums<=$resultSet2->returnNumItems() ? $resNums : $resultSet2->returnNumItems();
            //echo "BING COUNT ".$count."<br>";
            for($i=0; $i<$count;$i++ )
            {
                $this->resultSetAgg->addUrl($resultSet2->returnUrlsV2($i));
                $this->resultSetAgg->addTitle($resultSet2->returnTitlesV2($i));
                $this->resultSetAgg->addSnippet($resultSet2->returnSnippetsV2($i));
                $this->resultSetAgg->addScore($resultSet2->returnScoresV2($i)*$weight2);

            }
        }
        else if($resultSetFlag1 == TRUE)
        {
            $count = $resNums<=$resultSet1->returnNumItems() ? $resNums : $resultSet1->returnNumItems();
            for($i=0; $i<$count;$i++ )
            {
                $this->resultSetAgg->addUrl($resultSet1->returnUrlsV2($i));
                $this->resultSetAgg->addTitle($resultSet1->returnTitlesV2($i));
                $this->resultSetAgg->addSnippet($resultSet1->returnSnippetsV2($i));
                $this->resultSetAgg->addScore($resultSet1->returnScoresV2($i)*$weight1);
            }
        }
        else if ($resultSetFlag3 == TRUE)
        {
            $count = $resNums<=$resultSet3->returnNumItems() ? $resNums : $resultSet3->returnNumItems();
            for($i=0; $i<$count;$i++ )
            {
                $this->resultSetAgg->addUrl($resultSet3->returnUrlsV2($i));
                $this->resultSetAgg->addTitle($resultSet3->returnTitlesV2($i));
                $this->resultSetAgg->addSnippet($resultSet3->returnSnippetsV2($i));
                $this->resultSetAgg->addScore($resultSet3->returnScoresV2($i)*$weight3);
            }
        }
        else
        {
            echo '<br>Warning! No results from any Search Engines. Try Again Later.';
        }

        // ***********
        // Condition 1
        //************
        if($resultSetFlag2 == TRUE)
        {
            //
            // Fusion Stage 1
            //

            $countAL = $this->resultSetAgg->returnNumItems();
            //stripped urls  removes www/https/http from urls to match exact url
            $stripped_urls=aggregator::strip_urls($this->resultSetAgg->returnUrls());

            for($i=0, $count = $resultSet1->returnNumItems(); $i<$count;$i++ )
            {

                $stripped_url=aggregator::strip_url($resultSet1->returnUrlsV2($i));
                $idx=array_search($stripped_url,$stripped_urls , TRUE);
                if($idx!==FALSE){


                    $this->resultSetAgg->sumFusedScores($resultSet1->returnScoresV2($i), $idx, $weight1);
                }
                else{
                    $this->resultSetAgg->addUrl($resultSet1->returnUrlsV2($i));
                    $this->resultSetAgg->addTitle($resultSet1->returnTitlesV2($i));
                    $this->resultSetAgg->addSnippet($resultSet1->returnSnippetsV2($i));
                    $this->resultSetAgg->addScore($resultSet1->returnScoresV2($i)*$weight1);
                }


            }

            //
            // Fusion Stage 2
            //

            $countAL = $this->resultSetAgg->returnNumItems();
            $stripped_urls=aggregator::strip_urls($this->resultSetAgg->returnUrls());
            for($i=0, $count = $resultSet3->returnNumItems(); $i<$count;$i++ )
            {

                $idx=array_search(aggregator::strip_url($resultSet3->returnUrlsV2($i)),$stripped_urls , TRUE);
                if($idx!==false)
                {
                    $this->resultSetAgg->sumFusedScores($resultSet3->returnScoresV2($i), $idx, $weight3); //
                }
                else
                {
                    $this->resultSetAgg->addUrl($resultSet3->returnUrlsV2($i));
                    $this->resultSetAgg->addTitle($resultSet3->returnTitlesV2($i));
                    $this->resultSetAgg->addSnippet($resultSet3->returnSnippetsV2($i));
                    $this->resultSetAgg->addScore($resultSet3->returnScoresV2($i)*$weight3);

                    //  echo 'ASK '.(string)$this->$resultSet3->returnUrlsV2($i) . 'Bing '.(string)$this->resultSetAgg->returnUrlsV2($idx);

                }

            }
        } // End Cond 1

        // ***********
        // Condition 2
        //************
        if($resultSetFlag1 == TRUE && $resultSetFlag2 == FALSE)
        {
            //
            // Fusion Stage 1
            //

            $countAL = $this->resultSetAgg->returnNumItems();
            $stripped_urls=aggregator::strip_urls($this->resultSetAgg->returnUrls());
            for($i=0, $count = $resultSet3->returnNumItems(); $i<$count;$i++ )
            {
                for($j=0; $j<$countAL;$j++ )
                {

                    if(in_array(aggregator::strip_url($resultSet3->returnUrlsV2($i)),$stripped_urls, TRUE))
                    {
                        $this->resultSetAgg->sumFusedScores($resultSet3->returnScoresV2($i), $j, $weight3); //
                    }
                    else if(!in_array($resultSet3->returnUrlsV2($i), $this->resultSetAgg->returnUrls(), TRUE))
                    {
                        $this->resultSetAgg->addUrl($resultSet3->returnUrlsV2($i));
                        $this->resultSetAgg->addTitle($resultSet3->returnTitlesV2($i));
                        $this->resultSetAgg->addSnippet($resultSet3->returnSnippetsV2($i));
                        $this->resultSetAgg->addScore($resultSet3->returnScoresV2($i)*$weight3);
                    }
                }
            }
        } // End Cond 2


    } // End of Data Fusion Function

    public static function strip_url($url){
        $stripped=preg_replace('/(?:https?:\/\/)?(?:www\.)?/i',"",$url);
        //$stripped=preg_replace("/(?:https?:\/\/)?(?:www\.)/", "", $url);
        $idx=strrpos($stripped, '/');
        if($idx!=false&&$idx==strlen($stripped)-1)
            return substr($stripped,0, $idx);
        return $stripped;


    }

    public static function strip_urls($urls){
        $stripped=preg_replace('/(?:https?:\/\/)?(?:www\.)?/i',"",$urls);
        //$stripped=preg_replace("/(?:https?:\/\/)?(?:www\.)/", "", $url);
        return $stripped;


    }

    // Sort and Display Agg List
    public function printResultSetAgg() {

        // Sorting technique
        $sortedKeys = $this->resultSetAgg->returnScores();
        arsort($sortedKeys);
        $sortedKeys = array_keys($sortedKeys);

        // Iterate the sorted key array
        $i=0;
        if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
            foreach($this->resultSetAgg->returnUrls() as $key=>$value)
            {
                if($i % 4 == 0 && $i != 0) echo '<div class="row">';
                // Print the details according to the ordered array
                echo '<div class="span3"><a href="'.$this->resultSetAgg->returnUrlsV2($i).'"><img class="img-rounded" src="'.$this->resultSetAgg->returnUrlsV2($i).'" height="200" width="200" alt="'.$this->resultSetAgg->returnTitlesV2($i).'">'.'</a></div>';
                $i++;
                if($i % 4 == 0) echo '</div>';
            }
        }  else {
            foreach($this->resultSetAgg->returnUrls() as $item)
                // Print the details according to the ordered array
            {
                echo '<div class="span12">';
                echo '<br><strong>#'.($i+1).': '.$this->resultSetAgg->returnTitlesV2($sortedKeys[$i]).'</strong>';
                echo '<br>'.'<a href="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'">'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'</a>';
                echo '<br>'.$this->resultSetAgg->returnSnippetsV2($sortedKeys[$i]);
                echo '<br>Score: '.$this->resultSetAgg->returnScoresV2($sortedKeys[$i]).'<br>';
                echo '</div>';
                $i++;
            }
            echo '</div>';
        }

    }// End of printResultSetAgg()

    // Return Aggregated List Urls
    public function returnResultSetAggUrls() {
        return $this->resultSetAgg->returnUrls();
    }

    // Return Aggregated List Titles
    public function returnResultSetAggTitles() {
        return $this->resultSetAgg->returnTitles();
    }

    // Return Aggregated List Snippets
    public function returnResultSetAggSnippets() {
        return $this->resultSetAgg->returnSnippets();
    }

    // *****************************************************
    // Sort and Display Agg List according to Clustered Term
    // *****************************************************
    public function printResultSetAggCluster($clusterTerm) {

        //echo trim($clusterTerm).'<br>';
        $this->resultSetAggCluster = $this->resultSetAgg;

        // Filter By Clustered Term
        foreach($this->resultSetAgg->returnSnippets() as $key=>$value)
        {
            //replace non words with space
            $value = preg_replace('/[^\w]/', ' ', $value);
            $value = explode(' ', strtolower($value));

            if(!in_array($clusterTerm, $value))
                //if(strpos(strtolower($value), $clusterTerm ) == NULL)
                //if(strpos(strtolower(strip_tags($value)), (!empty($clusterTerm) ? $clusterTerm : " ") ) == NULL)
            {
                $this->resultSetAgg->unsetUrl($key);
                $this->resultSetAgg->unsetTitle($key);
                $this->resultSetAgg->unsetSnippet($key);
                $this->resultSetAgg->unsetScore($key);
            }
        }

        // Sorting technique required to sort unrelated arrays in parallel
        $sortedKeys = $this->resultSetAgg->returnScores();
        arsort($sortedKeys);
        $sortedKeys = array_keys($sortedKeys);

        $i=0;
        if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
            // Iterate the sorted key array
            for($i=0, $count = count($sortedKeys); $i<$count;$i++)
            {
                // Print the details according to the ordered array
                echo '<br>'.'<a href="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'"><img class="img-rounded" src="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'" height="200" width="200" alt="'.$this->resultSetAgg->returnTitlesV2($sortedKeys[$i]).'">'.'</a>';
                //echo '<br>'.$this->resultSetAgg->returnSnippetsV2($sortedKeys[$i]);
                //echo '<br>Score: '.$this->resultSetAgg->returnScoresV2($sortedKeys[$i]);
            }
        }  else {
            // Iterate the sorted key array
            for($i=0, $count = count($sortedKeys); $i<$count;$i++)
            {
                echo '<br><strong>#'.($i+1).': '.$this->resultSetAgg->returnTitlesV2($sortedKeys[$i]).'</strong>';
                echo '<br>'.'<a href="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'">'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'</a>';
                echo '<br>'.$this->resultSetAgg->returnSnippetsV2($sortedKeys[$i]).'<br>';
                //echo '<br>Score: '.$this->resultSetAgg->returnScoresV2($sortedKeys[$i]).'<br>';
            }
        }
    }// End of printResultSetAggCluster()

    // **********************
    // Display Binaclustering
    // **********************
    public function printResultSetAggBinCluster($binTerm, $bins, $binatureSums) {

        // Filter By Bins
        $tempBins = array_keys($bins);

        //for($i=0;$i<count($binatureSums);$i++)
        //{
        //echo '<br>!!!i: '.$i;
        //foreach($bins as $binKey=>$binValue)
        for($j=0;$j<count($bins);$j++)
        {
            //echo '<br>j: '.$j;
            //foreach($binValue as $Key=>$Value)
            for($k=0;$k<count($bins[$j]);$k++)
            {
                //echo '<br>k: '.$k;
                //echo '<br>V: '.$binKey.' T: '.$binTerm;
                //if($binTerm != $binKey)
                //echo '<br>Bin Contents: '.$bins[$j][$k];
                //echo '<br>BinTerm: '.$binTerm.' Bin Key j: '.array_keys($bins)[$j];
                if($binTerm != $tempBins[$j] && $binTerm != "")
                {
                    //echo '<br>!!!HIT Bin Array Key j: '.array_keys($bins)[$j];
                    //echo '<br>>>>Remove index: '.$bins[$j][$k];
                    //echo '<br>Bin Contents: '.$bins[$j][$k];
                    $this->resultSetAgg->unsetUrl($bins[$j][$k]);
                    $this->resultSetAgg->unsetTitle($bins[$j][$k]);
                    $this->resultSetAgg->unsetSnippet($bins[$j][$k]);
                    $this->resultSetAgg->unsetScore($bins[$j][$k]);
                }
            }
        }
        //}

        // Sorting technique required to sort unrelated arrays in parallel
        $sortedKeys = $this->resultSetAgg->returnScores();
        arsort($sortedKeys);
        $sortedKeys = array_keys($sortedKeys);

        if(isset($_SESSION['type']) && $_SESSION['type']=='image'){
            // Iterate the sorted key array
            for($i=0, $count = count($sortedKeys); $i<$count;$i++)
            {
                // Print the details according to the ordered array
                echo '<br>'.'<a href="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'"><img class="img-rounded" src="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'" height="200" width="200" alt="'.$this->resultSetAgg->returnTitlesV2($sortedKeys[$i]).'">'.'</a>';
                //echo '<br>'.$this->resultSetAgg->returnSnippetsV2($sortedKeys[$i]);
                //echo '<br>Score: '.$this->resultSetAgg->returnScoresV2($sortedKeys[$i]);
            }
        }  else {
            // Iterate the sorted key array
            for($i=0, $count = count($sortedKeys); $i<$count;$i++)
            {
                echo '<br><strong>#'.($i+1).': '.$this->resultSetAgg->returnTitlesV2($sortedKeys[$i]).'</strong>';
                echo '<br>'.'<a href="'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'">'.$this->resultSetAgg->returnUrlsV2($sortedKeys[$i]).'</a>';
                echo '<br>'.$this->resultSetAgg->returnSnippetsV2($sortedKeys[$i]).'<br>';
                //echo '<br>Score: '.$this->resultSetAgg->returnScoresV2($sortedKeys[$i]).'<br>';
            }
        }
    }// End of printResultSetAggBinCluster()

    // Output Result Set to File
    // Iterate the sorted key array
    public function outputResultSetToFile($filename, $resultSet, $int) {

        // Sorting technique
        $sortedKeys = $this->resultSetAgg->returnScores();
        arsort($sortedKeys);
        $sortedKeys = array_keys($sortedKeys);

        $target = $filename;
        $th=fopen(dirname(__FILE__).'/metrics/'.$target, 'a')or die("Couldn't open file, sorry");

        if($this->$resultSet->returnUrls() == NULL)
            echo '<br>No results!!!';
        else {
            for($i=0; $i<$int;$i++)
            {
                {
                    $line = ''.$this->$resultSet->returnUrlsV2($sortedKeys[$i]).PHP_EOL;
                    fwrite($th, $line);
                }
            }
        }
        fclose($th);
    }
} // End of Aggregator Class


// **************
// CLUSTER CLASS
// **************

// Purpose: To handle clustering of the aggregated list results
class cluster
{
    // **********
    // PROPERTIES
    // **********

    private $stringTokens = array();
    private $clusteredTerms = array();
    private $mostFrequentTerms = array();
    //
    private $masterBinature = "";
    private $binatures = array();
    private $binatureSums = array();
    private $bindroids = array();
    private $bins = array(array(),array(),array(),array());
    private $binTerms = array();
    private $clusteredBinaTerms = array();

    // **********
    // METHODS
    // **********

    // Tokenise
    public function tokeniseString($string){

        $stringTokens = NULL;
        //strip punctuation
        //$string = preg_replace('/(\'|&#0*39;)/', '', $string); // possibly related to a 's error "mccartney's" ie keeps possessive s
        $string = preg_replace('/[^a-z]+/i', ' ', $string);
        $this->stringTokens = explode(" ", strtolower($string));
        return $this->stringTokens;
    }

    // Display String Tokens
    public function displayStringTokens(){

        foreach($this->stringTokens as $item)
            echo '<br>'.$item;
    }

    // findTerms() is passed an array of snippets
    public function findTerms($array, $stopwords)
    {
        // foreach snippet
        foreach($array as $item)
        {
            $this->tokeniseString(strip_tags($item));
            //tokenise the snippet 
            foreach($this->stringTokens as $key=>$value)
            {
                //if the word in the snippet not in stopwords+querytokens then give it a value of 0
                if(!in_array($value, $stopwords, true))
                {
                    $this->clusteredTerms[$value]=0;
                    break;
                }
            }


        }
    }// End of find Terms

    // find Bina Terms() is passed an array of snippets
    public function findBinaTerms($array)
    {
        // foreach snippet
        foreach($array as $item)
        {
            $this->tokeniseString(strip_tags($item));

            foreach($this->stringTokens as $key=>$value)
            {
                // !!! Here
                $this->clusteredBinaTerms[$value]=1;
            }

            // Cluster Dictionary Word Fusion
            foreach($this->stringTokens as $key=>$value)
            {
                foreach($this->clusteredTerms as $key1=>$value1)
                {
                    if(!in_array($value, $this->clusteredBinaTerms, true))
                    {
                        $this->clusteredBinaTerms[$value] = 1;
                    }
                }
            }
        }
        sort($this->clusteredBinaTerms);
        //echo '<br>VARDUMP!!!';
        //var_dump(sort($this->clusteredBinaTerms));
    }// End of find BinaTerms

    // Print All Clustered Terms
    public function displayClusteredTerms(){

        echo '<h4>!!!Clustered Terms!!!</h4>';
        foreach($this->clusteredTerms as $key=>$value){
            echo '<br>'.$key.' ('.$value.')';
        }
    }

    // Count Cluster Frequency
    public function countTermFrequency($array){

        foreach($this->clusteredTerms as $termKey=>$termValue)
        {
            foreach($array as $stringKey=>$stringValue)
            {
                $this->tokeniseString($stringValue);
                foreach($this->stringTokens as $tokenKey=>$tokenValue)
                {
                    if($termKey==$tokenValue)
                    {
                        $this->clusteredTerms[$termKey] = ++$termValue;
                    }
                }
            }
        }
    }

    // Top X Clustered Terms where X is a passed int - could be user selectable
    public function setMostFrequentTerms($int){

        $topTerms = arsort($this->clusteredTerms);
        $i=1;
        foreach($this->clusteredTerms as $key=>$value)
        {
            array_push($this->mostFrequentTerms, $key = preg_replace('/[^a-z]+/i', ' ', $key));
            if($i++>=$int)
                break;
        }
    }

    // Stopword Removal
    public function stopwordRemoval($stopwordArray){

        // check to see if term is in stopword array, if it is, remove it by array pop
        foreach($this->mostFrequentTerms as $termKey=>$termValue)
        {
            foreach($stopwordArray as $stopKey=>$stopValue)
            {
                if($termValue == $stopValue)
                {
                    unset($this->mostFrequentTerms[$termKey]);
                }
            }
        }
    }

    // Display Most Frequent Terms
    public function displayMostFrequentTerms($q){
        echo '<br>'.'<img style="padding-right:10px;"src="static/img/folder.gif" width="18px"alt="folder icon"/><a href ="search.php?q='.$_SESSION['query'].'&result_op=clustered&term=">All Results</a>';
        foreach($this->mostFrequentTerms as $key=>$value){
            echo '<br>'.'<img style="padding-left:5px;padding-right:10px;"src="static/img/folder.gif" width="18px"alt="folder icon"/><a href ="search.php?q='.$_SESSION['query'].'&result_op=clustered&term='.$value.'">'.$value.'</a>';
        }
    }

    // Return Most Frequent Terms
    public function returnMostFrequentTerms(){
        return $this->mostFrequentTerms;
    }

    // *****************
    // Binaclusteing
    // *****************

    // 1. Set master List - use Clustered Terms above
    // 2. Set Bindroids


    // get count clusteredTerms
    public function countClusteredTerms(){
        return count($this->clusteredTerms);
    }

    // Return Most Frequent Terms
    public function returnClusteredTerms(){
        return $this->clusteredTerms;
    }

    // getMinBinatureSums
    public function getMinBinatureSums(){
        return min($this->binatureSums);
    }

    // getMaxBinatureSums
    public function getMaxBinatureSums(){
        return max($this->binatureSums);
    }

    // setDocumentBinatures
    public function setDocumentBinatures($snippets){
        //echo '<br>Setting Binatures...<br>';
        foreach($snippets as $snippet)
        {
            $i=0;
            $tempsum=0;
            $tempBinature = "";
            foreach($this->clusteredBinaTerms as $key=>$value)
            {
                if(strpos($snippet, $key) != NULL)
                {
                    //echo '<br>found';
                    $tempBinature[$i] = 1;
                    $tempsum++;
                }
                else
                {
                    $tempBinature[$i] = 0;
                }
                $i++;
            }
            //binatureSums contain sum of cluster terms occurances in each snippet
            array_push($this->binatureSums, $tempsum);
            $tempBinature = implode($tempBinature);
            //echo '<br>'.$tempBinature;
            //binatures contain a string that represents which terms found in each snippet with 1-0 
            array_push($this->binatures, $tempBinature);
        }
    }

    // Print binatures
    public function printBinatures(){
        foreach ($this->binatures as $binature)
        {
            echo '<br>'.$binature;
        }
    }

    // Print binatureSums
    public function printBinatureSums(){
        foreach ($this->binatureSums as $sum)
        {
            echo '<br>'.$sum;
        }
    }

    // Print binatureSums
    public function returnBinatureSums(){
        return $this->binatureSums;
    }

    // set Bindroids
    public function setBindroids($ticks){
        $range = ($this->getMaxBinatureSums() - $this->getMinBinatureSums());
        //echo '<br>Min: '.$this->getMinBinatureSums();
        //echo '<br>Max: '.$this->getMaxBinatureSums();
        //echo '<br>Range: '.$range;
        //echo '<br>Ticks: '.$ticks;
        for($i=0;$i<$ticks;$i++)
        {
            $this->bindroids[$i] = ($this->getMinBinatureSums() + (($range/($ticks+1)) * ($i+1)));
        }
        $this->bindroids[$i] = $this->getMaxBinatureSums();
        //echo '<br>Bindroids: ';var_dump($this->bindroids);
    }

    // Bin Binatures
    public function binBinatures($int){
        foreach ($this->binatureSums as $binSumKey=>$binSumValue)
        {
            //echo '<br>$binSumValue: '.$binSumValue;
            //foreach($this->bindroids as $bindroid)
            for($i=0;$i<=$int;$i++)
            {
                //echo '<br>$bindroid: '.$this->bindroids[$i];
                if($binSumValue <= $this->bindroids[$i])
                {
                    //echo '<br>!!!Hit: '.$i;
                    array_push($this->bins[$i], $binSumKey);
                    break;
                }
            }
        }
        //echo '<br>Bins: ';var_dump($this->bins);
    }

    // Bin Terms
    public function returnBins(){

        return $this->bins;
    }

    // Bin Terms
    public function setBinTerms($int){
        for($i=0;$i<$int;$i++)
            $this->binTerms[$i]="".$i;
        //$this->binTerms = array("0", "1", "2", "3");
    }

    // Display Most Frequent Bin Terms
    public function displayBinTerms($q){
        echo '<br>'.'<img style="padding-right:10px;"src="static/img/folder.gif" width="18px"alt="folder icon"/><a href ="search.php?q='.$_SESSION['query'].'&result_op=clustered&binTerm=">All Results</a>';
        foreach($this->binTerms as $key=>$value){
            echo '<br>'.'<img style="padding-left:5px;padding-right:10px;"src="static/img/folder.gif" width="18px"alt="folder icon"/><a href ="search.php?q='.$_SESSION['query'].'&result_op=clustered&binTerm='.$value.'">Cluster: '.$value.'</a>';
        }
    }

} // End of Cluster Class


// *****************
// DICTIONARY CLASS
// *****************

// Purpose: To handle all matters to do with dictionaries such as stop word lists
class dictionary
{
    // **********
    // PROPERTIES
    // **********
    private $stopwordFilename;
    private $stopwords;


    // **********
    // CONSTRUCTOR
    // **********
    public function __construct($stopwordFilename) {
        $this->stopwordFilename = $stopwordFilename;
        $this->loadStopwordFile();
    }

    // **********
    // METHODS
    // **********

    // load Stopword File
    // Maybe pass in name of file se we can reuse the function to add custom lists
    public function loadStopwordFile(){

        $fp = fopen(dirname(__FILE__).'/utilities/'.$this->stopwordFilename, 'r') or die("Couldn\'t open file, sorry");
        while (!feof($fp))
        {
            $line=fgets($fp);
            $this->stopwords[] = trim($line);
        }
        fclose($fp);
    }

    // Add Query to Stopwords
    public function addQueryToStopwords($queryTokens){

        foreach($queryTokens as $key=>$value)
        {
            //add to array
            $this->stopwords[] = trim($value);
        }
    }

    // List stopwords
    public function displayStopwordFile(){
        foreach($this->stopwords as $word)
        {
            echo '<br>'.$word;
        }
        return $this->stopwords;
    }

    // Return stopwords
    public function returnStopwords(){
        return $this->stopwords;
    }

} // End of Dictionary Class


// **********
// THESAURUS CLASS
// **********

// Purpose: To handle all matters to do with THESAURUS
class thesaurus
{

    // **********
    // METHODS
    // **********

    // load Thesaurus File
    public function loadThesaurusFile($filename){

        $fp = fopen(dirname(__FILE__).'/utilities/'.$filename, 'r') or die("Couldn\'t open file, sorry");
        $line=fgets($fp);
        while (!feof($fp))
        {
            //$part1 is a word $part2 is the whole line 
            list($part1, $part2) = explode(',', $line, 2);
            // Next line shortens synonyms to just one synonym
            //list($part2, $part3) = explode(',', $part2, 2);
            //add to array
            $this->thesaurus[$part1] = trim($part2);
            $line=fgets($fp);
        }
        fclose($fp);
    }

    // Return Thesaurus
    public function returnThesaurus(){
        return $this->thesaurus;
    }
}

?>