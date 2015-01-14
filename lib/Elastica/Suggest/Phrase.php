<?php

namespace Elastica\Suggest;

use Elastica\Suggest\CandidateGenerator\AbstractCandidateGenerator;

/**
 * Class Phrase
 * @package Elastica\Suggest
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html
 */
class Phrase extends AbstractSuggest
{
    /**
     * @param  string                   $analyzer
     * @return \Elastica\Suggest\Phrase
     */
    public function setAnalyzer($analyzer)
    {
        return $this->setParam("analyzer", $analyzer);
    }

    /**
     * Set the max size of the n-grams (shingles) in the field
     * @param  int                      $size
     * @return \Elastica\Suggest\Phrase
     */
    public function setGramSize($size)
    {
        return $this->setParam("gram_size", $size);
    }

    /**
     * Set the likelihood of a term being misspelled even if the term exists in the dictionary
     * @param  float                    $likelihood Defaults to 0.95, meaning 5% of the words are misspelled.
     * @return \Elastica\Suggest\Phrase
     */
    public function setRealWordErrorLikelihood($likelihood)
    {
        return $this->setParam("real_word_error_likelihood", $likelihood);
    }

    /**
     * Set the factor applied to the input phrases score to be used as a threshold for other suggestion candidates.
     * Only candidates which score higher than this threshold will be included in the result.
     * @param  float                    $confidence Defaults to 1.0.
     * @return \Elastica\Suggest\Phrase
     */
    public function setConfidence($confidence)
    {
        return $this->setParam("confidence", $confidence);
    }

    /**
     * Set the maximum percentage of the terms considered to be misspellings in order to form a correction
     * @param  float                    $max
     * @return \Elastica\Suggest\Phrase
     */
    public function setMaxErrors($max)
    {
        return $this->setParam("max_errors", $max);
    }

    /**
     * @param  string          $separator
     * @return \Elastica\Param
     */
    public function setSeparator($separator)
    {
        return $this->setParam("separator", $separator);
    }

    /**
     * Set suggestion highlighting
     * @param  string                   $preTag
     * @param  string                   $postTag
     * @return \Elastica\Suggest\Phrase
     */
    public function setHighlight($preTag, $postTag)
    {
        return $this->setParam("highlight", array(
            'pre_tag' => $preTag,
            'post_tag' => $postTag,
        ));
    }

    /**
     * @param  float                    $discount
     * @return \Elastica\Suggest\Phrase
     */
    public function setStupidBackoffSmoothing($discount = 0.4)
    {
        return $this->setSmoothingModel("stupid_backoff", array(
            "discount" => $discount,
        ));
    }

    /**
     * @param  float                    $alpha
     * @return \Elastica\Suggest\Phrase
     */
    public function setLaplaceSmoothing($alpha = 0.5)
    {
        return $this->setSmoothingModel("laplace", array(
            "alpha" => $alpha,
        ));
    }

    /**
     * @param  float                    $trigramLambda
     * @param  float                    $bigramLambda
     * @param  float                    $unigramLambda
     * @return \Elastica\Suggest\Phrase
     */
    public function setLinearInterpolationSmoothing($trigramLambda, $bigramLambda, $unigramLambda)
    {
        return $this->setSmoothingModel("linear_interpolation", array(
            "trigram_lambda" => $trigramLambda,
            "bigram_lambda" => $bigramLambda,
            "unigram_lambda" => $unigramLambda,
        ));
    }

    /**
     * @param  string                   $model  the name of the smoothing model
     * @param  array                    $params
     * @return \Elastica\Suggest\Phrase
     */
    public function setSmoothingModel($model, array $params)
    {
        return $this->setParam("smoothing", array(
            $model => $params,
        ));
    }

    /**
     * @param  AbstractCandidateGenerator $generator
     * @return \Elastica\Suggest\Phrase
     */
    public function addCandidateGenerator(AbstractCandidateGenerator $generator)
    {
        $generator = $generator->toArray();
        $keys = array_keys($generator);
        $values = array_values($generator);

        return $this->addParam($keys[0], $values[0]);
    }
}
