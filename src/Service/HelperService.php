<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class HelperService
{
    // format the date to a DateTime object if possible. Return false instead
    public function formatBirthdate($dateString)
    {
        if(trim($dateString) === '') {
            return false;
        }

        // change the date dividers so that strtotime function won't use the american date notation
        $dateString = trim(str_replace('/', '-', $dateString));
        $unix = strtotime($dateString);

        if(!$unix) {
            return false;
        }

        $date = new \DateTime(date('d-m-Y', $unix));
        return $date;
    }

    public function setFlashMessage($message, Request $request)
    {
        $flashError = array('message' => $message);
        $request->getSession()->set('flash', $flashError);
    }

    /**
     * @param $file
     * @return array|bool
     */
    public function parseCsv($file)
    {
        $data = array();
        if (($handle = fopen($file, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
                $data[] = $row;
            }
            fclose($handle);
        }

        return (empty($data)) ? false : $data;
    }

    public function generateNumber()
    {
        $numbers = range(1000, 30000);
        $i = rand(0, count($numbers) - 1);

        return $numbers[$i];
    }

    public function generateDate()
    {
        $days = range(1, 28);
        $months = range(1, 12);
        $years = range(1930, 2000);

        $daysI = rand(0, count($days) - 1);
        $monthsI = rand(0, count($months) - 1);
        $yearsI = rand(0, count($years) - 1);

        return $days[$daysI] . '/' . $months[$monthsI] . '/' . $years[$yearsI];
    }
}