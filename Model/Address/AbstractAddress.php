<?php

namespace AstralWeb\ModifyAddress\Model\Address;

class AbstractAddress extends \Magento\Customer\Model\Address\AbstractAddress
{
   /**
     * Validate address attribute values.
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        $errors = [];
        if (!\Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'firstname']);
        }

        if (!\Zend_Validate::is($this->getStreetLine(1), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'street']);
        }

        if (!\Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'city']);
        }

        if ($this->isTelephoneRequired()) {
            if (!\Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', ['fieldName' => 'telephone']);
            }
        }

        if ($this->isFaxRequired()) {
            if (!\Zend_Validate::is($this->getFax(), 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', ['fieldName' => 'fax']);
            }
        }

        if ($this->isCompanyRequired()) {
            if (!\Zend_Validate::is($this->getCompany(), 'NotEmpty')) {
                $errors[] = __('%fieldName is a required field.', ['fieldName' => 'company']);
            }
        }

        $_havingOptionalZip = $this->_directoryData->getCountriesWithOptionalZip();
        if (!in_array(
            $this->getCountryId(),
            $_havingOptionalZip
        ) && !\Zend_Validate::is(
            $this->getPostcode(),
            'NotEmpty'
        )
        ) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'postcode']);
        }

        $countryId = $this->getCountryId();
        if (!\Zend_Validate::is($countryId, 'NotEmpty')) {
            $errors[] = __('%fieldName is a required field.', ['fieldName' => 'countryId']);
        } else {
            //Checking if such country exists.
            $countryCollection = $this->_directoryData->getCountryCollection($this->getStoreId());
            if (!in_array($countryId, $countryCollection->getAllIds(), true)) {
                $errors[] = __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    [
                        'fieldName' => 'countryId',
                        'value' => htmlspecialchars($countryId)
                    ]
                );
            } else {
                //If country is valid then validating selected region ID.
                $countryModel = $this->getCountryModel();
                $regionCollection = $countryModel->getRegionCollection();
                $region = $this->getRegion();
                $regionId = (string)$this->getRegionId();
                $allowedRegions = $regionCollection->getAllIds();
                $isRegionRequired = $this->_directoryData->isRegionRequired($countryId);
                if ($isRegionRequired && empty($allowedRegions) && !\Zend_Validate::is($region, 'NotEmpty')) {
                    //If region is required for country and country doesn't provide regions list
                    //region must be provided.
                    $errors[] = __('%fieldName is a required field.', ['fieldName' => 'region']);
                } elseif ($allowedRegions && !\Zend_Validate::is($regionId, 'NotEmpty') && $isRegionRequired) {
                    //If country actually has regions and requires you to
                    //select one then it must be selected.
                    $errors[] = __('%fieldName is a required field.', ['fieldName' => 'regionId']);
                } elseif ($allowedRegions && $regionId && !in_array($regionId, $allowedRegions, true)) {
                    //If a region is selected then checking if it exists.
                    $errors[] = __(
                        'Invalid value of "%value" provided for the %fieldName field.',
                        [
                            'fieldName' => 'regionId',
                            'value' => htmlspecialchars($regionId)
                        ]
                    );
                }
            }
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }

        return $errors;
    }
}