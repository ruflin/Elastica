<?php

namespace Elastica\Suggest;

use Elastica\Suggest\CandidateGenerator\AbstractCandidateGenerator;
use Elastica\Suggest\CandidateGenerator\DirectGenerator;

/**
 * Class Phrase.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html
 */
class Phrase extends AbstractSuggest
{
    public const DEFAULT_REAL_WORD_ERROR_LIKELIHOOD = 0.95;
    public const DEFAULT_CONFIDENCE = 1.0;
    public const DEFAULT_MAX_ERRORS = 1.0;
    public const DEFAULT_STUPID_BACKOFF_DISCOUNT = 0.4;
    public const DEFAULT_LAPLACE_SMOOTHING_ALPHA = 0.5;

    /**
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Set the max size of the n-grams (shingles) in the field.
     *
     * @return $this
     */
    public function setGramSize(int $size): self
    {
        return $this->setParam('gram_size', $size);
    }

    /**
     * Set the likelihood of a term being misspelled even if the term exists in the dictionary.
     *
     * @param float $likelihood Defaults to 0.95, meaning 5% of the words are misspelled.
     *
     * @return $this
     */
    public function setRealWordErrorLikelihood(float $likelihood): self
    {
        return $this->setParam('real_word_error_likelihood', $likelihood);
    }

    /**
     * Set the factor applied to the input phrases score to be used as a threshold for other suggestion candidates.
     * Only candidates which score higher than this threshold will be included in the result.
     *
     * @param float $confidence Defaults to 1.0.
     *
     * @return $this
     */
    public function setConfidence(float $confidence): self
    {
        return $this->setParam('confidence', $confidence);
    }

    /**
     * Set the maximum percentage of the terms considered to be misspellings in order to form a correction.
     *
     * @return $this
     */
    public function setMaxErrors(float $max): self
    {
        return $this->setParam('max_errors', $max);
    }

    /**
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        return $this->setParam('separator', $separator);
    }

    /**
     * Set suggestion highlighting.
     *
     * @return $this
     */
    public function setHighlight(string $preTag, string $postTag): self
    {
        return $this->setParam('highlight', [
            'pre_tag' => $preTag,
            'post_tag' => $postTag,
        ]);
    }

    /**
     * @return $this
     */
    public function setStupidBackoffSmoothing(float $discount): self
    {
        return $this->setSmoothingModel('stupid_backoff', [
            'discount' => $discount,
        ]);
    }

    /**
     * @return $this
     */
    public function setLaplaceSmoothing(float $alpha): self
    {
        return $this->setSmoothingModel('laplace', [
            'alpha' => $alpha,
        ]);
    }

    /**
     * @return $this
     */
    public function setLinearInterpolationSmoothing(float $trigramLambda, float $bigramLambda, float $unigramLambda): self
    {
        return $this->setSmoothingModel('linear_interpolation', [
            'trigram_lambda' => $trigramLambda,
            'bigram_lambda' => $bigramLambda,
            'unigram_lambda' => $unigramLambda,
        ]);
    }

    /**
     * @param string               $model  the name of the smoothing model
     * @param array<string, mixed> $params
     *
     * @return $this
     */
    public function setSmoothingModel(string $model, array $params): self
    {
        return $this->setParam('smoothing', [
            $model => $params,
        ]);
    }

    /**
     * @return $this
     */
    public function addDirectGenerator(DirectGenerator $generator): self
    {
        return $this->addParam('candidate_generator', $generator);
    }

    /**
     * @deprecated since version 7.2.0, use the "addDirectGenerator()" method instead.
     *
     * @return $this
     */
    public function addCandidateGenerator(AbstractCandidateGenerator $generator): self
    {
        \trigger_deprecation('ruflin/elastica', '7.2.0', 'The "%s()" method is deprecated, use the "addDirectGenerator()" method instead. It will be removed in 8.0.', __METHOD__);

        return $this->addParam('candidate_generator', $generator);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['candidate_generator'])) {
            $generators = $array[$baseName]['candidate_generator'];
            unset($array[$baseName]['candidate_generator']);

            foreach ($generators as $generator) {
                $key = \array_key_first($generator);
                $array[$baseName][$key][] = $generator[$key];
            }
        }

        return $array;
    }
}
