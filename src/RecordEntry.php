<?php

namespace Balsama\Tempbot;

/**
 * Enforces the order that the spreadsheet expects the readings in.
 */
class RecordEntry
{
    public function __construct(
        public string $date,
        public ?float $outsideTemp,
        public ?float $outsideHumidity,
        public ?float $tb0101t,
        public ?float $tb0201t,
        public ?float $tb0301t,
        public ?float $tb0302t,
        public ?float $tb0401t,
        public ?float $tb0101h,
        public ?float $tb0201h,
        public ?float $tb0301h,
        public ?float $tb0302h,
        public ?float $tb0401h,
    ) {
    }


    public function getArray(): array
    {
        return [
            'date' => $this->date,
            'outsidet' => $this->outsideTemp,
            'outsideh' => $this->outsideHumidity,
            'TB0101t' => $this->tb0101t,
            'TB0201t' => $this->tb0201t,
            'TB0301t' => $this->tb0301t,
            'TB0302t' => $this->tb0302t,
            'TB0401t' => $this->tb0401t,
            'TB0101h' => $this->tb0101h,
            'TB0201h' => $this->tb0201h,
            'TB0301h' => $this->tb0301h,
            'TB0302h' => $this->tb0302h,
            'TB0401h' => $this->tb0401h,
        ];
    }
}
