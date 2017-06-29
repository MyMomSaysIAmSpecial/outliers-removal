<?php

namespace Service;

/**
 * Class OutliersHandler
 * @package Service
 */
class OutliersHandler
{
    /**
     * @var array
     */
    private $dataset;

    /**
     * @param array $dataset
     */
    public function setDataset(array $dataset)
    {
        $this->dataset = $dataset;
    }

    /**
     * @return array
     */
    public function getFilteredDataset(): array
    {
        # Sorting dataset in ascending order
        $dataset = $this->dataset;
        sort($dataset, SORT_NUMERIC);

        $bottomQuartile = $this->getBottomQuartile($dataset);
        $upperQuartile = $this->getUpperQuartile($dataset);

        $interQuartileRange = $this->getInterquartileRange($upperQuartile, $bottomQuartile);

        $innerFences = $this->getInnerFences($interQuartileRange, $bottomQuartile, $upperQuartile);
        $outerFences = $this->getOuterFences($interQuartileRange, $bottomQuartile, $upperQuartile);

        return [
            'minor' => array_filter(
                $dataset,
                function($value) use ($innerFences) {
                    return $value > min($innerFences) && $value < max($innerFences);
                }
            ),
            'major' => array_filter(
                $dataset,
                function($value) use ($outerFences) {
                    return $value > min($outerFences) && $value < max($outerFences);
                }
            )
        ];
    }

    /**
     * Return Q2
     *
     * @param array $dataset sorted in ascending order
     * @return float
     * @throws \Exception
     */
    private function getMedian(array $dataset): float
    {
        $count = count($dataset);

        if (!$count) {
            throw new \Exception('Data set cannot be empty');
        }

        $middle = (int)floor($count / 2);

        # If even
        if ($count % 2 === 0) {
            return ($dataset[$middle] + $dataset[$middle - 1]) / 2;
        }

        return $dataset[$middle];
    }

    /**
     * Return Q1
     *
     * @param array $dataset sorted in ascending order
     * @return float
     * @throws \Exception
     */
    private function getBottomQuartile(array $dataset): float
    {
        $count = count($dataset);

        if (!$count) {
            throw new \Exception('Data set cannot be empty');
        }

        $i = 0;
        $half = [];
        $middle = (int)floor($count / 2);

        # If even
        if ($count % 2 === 0) {
            while ($i < $middle) {
                $half[] = $dataset[$i];
                $i++;
            }
        } else {
            while ($i < $middle) {
                $half[] = $dataset[$i];
                $i++;
            }
        }

        sort($half);

        return $this->getMedian($half);
    }

    /**
     * Return Q3
     *
     * @param array $dataset sorted in ascending order
     * @return float
     * @throws \Exception
     */
    private function getUpperQuartile(array $dataset): float
    {
        $count = count($dataset);

        if (!$count) {
            throw new \Exception('Data set cannot be empty');
        }

        $i = $count - 1;
        $half = [];
        $middle = (int)floor($count / 2);

        # If even
        if ($count % 2 === 0) {
            while ($i >= $middle) {
                $half[] = $dataset[$i];
                $i--;
            }
        } else {
            while ($i > $middle) {
                $half[] = $dataset[$i];
                $i--;
            }
        }

        sort($half);

        return $this->getMedian($half);
    }

    /**
     * Return Q3 - Q1
     *
     * @param float $upperQuartile
     * @param float $bottomQuartile
     * @return float
     */
    private function getInterquartileRange(float $upperQuartile, float $bottomQuartile): float
    {
        return $upperQuartile - $bottomQuartile;
    }

    /**
     * @param $interQuartileRange
     * @param $bottomQuartile
     * @param $upperQuartile
     * @return array
     */
    private function getInnerFences(float $interQuartileRange, float $bottomQuartile, float $upperQuartile): array
    {
        return [
            $bottomQuartile - $interQuartileRange * 1.5 ,
            $upperQuartile + $interQuartileRange * 1.5,
        ];
    }

    /**
     * @param $interQuartileRange
     * @param $bottomQuartile
     * @param $upperQuartile
     * @return array
     */
    private function getOuterFences(float $interQuartileRange, float $bottomQuartile, float $upperQuartile): array
    {
        return [
            $bottomQuartile - $interQuartileRange * 3,
            $upperQuartile + $interQuartileRange * 3,
        ];
    }
}
