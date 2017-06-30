<?php
//MS office schema https://msdn.microsoft.com/en-us/library/bb226687(v=office.11).aspx
//http://m8y.org/Microsoft_Office_2003_XML_Reference_Schemas/Help/html/spreadsheetml_HV01151864.htm
class ModelToolSndatatooltransmit extends Model {
    
    //http://php.net/manual/ru/features.file-upload.post-method.php
    public function processFileUpload() {
        if ($isset($_FILES['userfiles']['name']) == FALSE ) {
            //seems like that error has happened, no file to process
            return; }
        
    }
    /**
     * validate XML metadata. It should be in Microsoft Excel 2003 XML semantics. Luckily MSOffice gives some hints to identify this
     */
    public function validateXMLmetadata($fileaddress) {
        //see language file  admin\language\en-gb\tool. Seems like it is not appreciated to load language file in model
        //actually it is not good. 
        $log_additions = array();
        //$log_additions[] = 'VAR_log_validate_xml_structure_1';
        //https://stackoverflow.com/questions/27535504/how-to-parse-a-large-xml-file
        //https://ikfi.ru/article/parsim-xml-s-pomoschju-xmlreader
        $reader = new XMLReader();
        $reader->open($fileaddress);
        $expectedMetadataValues=array();
        $expectedMetadataValues['xmlns']    = 'urn:schemas-microsoft-com:office:spreadsheet';
        $expectedMetadataValues['xmlns_c']  = 'urn:schemas-microsoft-com:office:component:spreadsheet';
        $expectedMetadataValues['xmlns_o']  = 'urn:schemas-microsoft-com:office:office';
        $expectedMetadataValues['xmlns_ss'] = 'urn:schemas-microsoft-com:office:spreadsheet';
        $expectedMetadataValues['xmlns_x2'] = 'http://schemas.microsoft.com/office/excel/2003/xml';
        $expectedMetadataValues['xmlns_x']  = 'urn:schemas-microsoft-com:office:excel';
        $actualMetadataValues = array();
        $actualMetadataValues['xmlns']    = FALSE;
        $actualMetadataValues['xmlns_c']  = FALSE;
        $actualMetadataValues['xmlns_o']  = FALSE;
        $actualMetadataValues['xmlns_ss'] = FALSE;
        $actualMetadataValues['xmlns_x2'] = FALSE;
        $actualMetadataValues['xmlns_x']  = FALSE;
        
        $xmlns = ""; $xmlns_x = ""; $xmlns_x2 = ""; $xmlns_c = ""; $xmlns_o = ""; $xmlns_ss="";
        $evalrate = 0; 
        define('VALIDATION_PASSED_SCORE', 3);
        
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT) {
                if ($reader->localName == "Workbook") {
                    $xmlns    = $reader->getAttribute('xmlns');
                    $xmlns_c = $reader->getAttribute('xmlns:c');
                    $xmlns_o = $reader->getAttribute('xmlns:o');
                    $xmlns_ss = $reader->getAttribute('xmlns:ss');
                    $xmlns_x2 = $reader->getAttribute('xmlns:x2');
                    $xmlns_x = $reader->getAttribute('xmlns:x');
                    if ($xmlns == $expectedMetadataValues['xmlns']) { $actualMetadataValues['xmlns'] = TRUE; $evalrate++; }
                    if ($xmlns_c == $expectedMetadataValues['xmlns_c']) { $actualMetadataValues['xmlns_c'] = TRUE; $evalrate++; }
                    if ($xmlns_o == $expectedMetadataValues['xmlns_o']) { $actualMetadataValues['xmlns_o'] = TRUE; $evalrate++; }
                    if ($xmlns_ss == $expectedMetadataValues['xmlns_ss']) { $actualMetadataValues['xmlns_ss'] = TRUE; $evalrate++; }
                    if ($xmlns_x2 == $expectedMetadataValues['xmlns_x2']) { $actualMetadataValues['xmlns_x2'] = TRUE; $evalrate++; }
                    if ($xmlns_x == $expectedMetadataValues['xmlns_x']) { $actualMetadataValues['xmlns_x'] = TRUE; $evalrate++; }
                    $log_additions['xml_validation_passed']=FALSE;
                    if ($evalrate>=VALIDATION_PASSED_SCORE) {$log_additions['xml_validation_passed']=true; }
                    break;
                }
            }
        }
        $log_additions['validation_detail'] = $actualMetadataValues;
        return $log_additions;
    }
    /**
     * acquire some data from SpreadsheetML document, using settings
     * @param string $fileaddress
     * @param structure $initial_settings - huge associative array
     */
    public function dataAcqusitionFromXML($fileaddress, $initial_settings) {
        
    }
    
}
?>

