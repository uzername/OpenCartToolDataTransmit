<?php
//this file is intended to implement business logic of XML import tool (and export tool too, some day)
//written by example as of \admin\controller\tool\log.php
//controller component of sndatatooltransmit: toss it to \admin\controller\tool\

//Appropriate values of $data['the_stage'] :
//'DEFAULT' - just started. 'AFTER_VALIDATION' - render button 
class ControllerToolSndatatooltransmit extends Controller {
        //it is better to keep file processing routine here, because POST is being processed here
        //location of file
        public $upload_address=DIR_UPLOAD."xmltool/upload/";
        
        /**
         * complete path to file on server (path+fname)
         * @var string
         */
        public $upload_file="";
    
	private $error = array();
        // $data array is defined here?
        private function basicDataArrayGeneration() {
            //is it ok that this routine is called multiple times during execution of class
            $this->load->language('tool/sndatatooltransmit');
            
                $data['heading_title'] = $this->language->get('heading_title');
		$data['text_file'] = $this->language->get('text_file');
                $data['text_file_pc'] = $this->language->get('text_file_pc');
                $data['text_file_server'] = $this->language->get('text_file_server');
                $data['text_file_other'] = $this->language->get('text_file_other');
                $data['text_file_resultscapt'] = $this->language->get('text_file_resultscapt');  
                $data['text_file_remove_afteruse'] = $this->language->get('text_file_remove_afteruse');
                $data['the_stage'] = 'DEFAULT'; //stage of transmission
                
		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
                //path metadata. do not fiddle with this
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tool/sndatatooltransmit', 'token=' . $this->session->data['token'], true)
		);
                //end of fragment
                //lines for components of admin gui. do not fiddle with this
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
                //end of fragment                
                $data['processdataimport'] = $this->url->link('tool/sndatatooltransmit/processdataimport', 'token=' . $this->session->data['token'], true);            
            return $data;
        }
	public function index() {		
		$this->load->language('tool/sndatatooltransmit');
		
		$this->document->setTitle($this->language->get('heading_title'));
		$data = $this->basicDataArrayGeneration();                
                $data['the_stage'] = 'DEFAULT';
		$this->response->setOutput($this->load->view('tool/sndatatooltransmit', $data));
	}
        /* KEEP THESE JUST FOR REFERENCE, REMOVE LATER, PROBABLY */
	public function download() {
		$this->load->language('tool/sndatatooltransmit');

		$file = DIR_LOGS . $this->config->get('config_error_filename');

		if (file_exists($file) && filesize($file) > 0) {
			$this->response->addheader('Pragma: public');
			$this->response->addheader('Expires: 0');
			$this->response->addheader('Content-Description: File Transfer');
			$this->response->addheader('Content-Type: application/octet-stream');
			$this->response->addheader('Content-Disposition: attachment; filename="' . $this->config->get('config_name') . '_' . date('Y-m-d_H-i-s', time()) . '_error.log"');
			$this->response->addheader('Content-Transfer-Encoding: binary');

			$this->response->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
		} else {
			$this->session->data['error'] = sprintf($this->language->get('error_warning'), basename($file), '0B');

			$this->response->redirect($this->url->link('tool/sndatatooltransmit', 'token=' . $this->session->data['token'], true));
		}
	}
	public function clear() {
		$this->load->language('tool/log');

		if (!$this->user->hasPermission('modify', 'tool/sndatatooltransmit')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			$file = DIR_LOGS . $this->config->get('config_error_filename');

			$handle = fopen($file, 'w+');

			fclose($handle);

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->response->redirect($this->url->link('tool/sndatatooltransmit', 'token=' . $this->session->data['token'], true));
	}
        /* ==================================================================== */
        //here comes data processing
        public function processdataimport() {
            $this->load->model("tool/sndatatooltransmit");
            $data = $this->basicDataArrayGeneration();
            $data['append_to_log']=true;
            $data['log_lines']=array();
            $debug_use_processing = FALSE; 
            
            if ($debug_use_processing == FALSE) {
                //print_r($_FILES);
                //print_r($_POST['uploadoption']);
                /* following fragment depends on frontend. 
                 * 'xml' is name of form field where the file is uploaded. 'uploadoption' is name of radiogroup */
                if ( ($_POST['uploadoption'][0] == 'pc')&&(isset($_FILES['xml']['name'])) ) {
                    $this->upload_file = $this->upload_address.basename($_FILES['xml']['name']);
                    
                    if (is_dir($this->upload_address) == FALSE) {
                        mkdir($this->upload_address,0777,TRUE);
                    }
                    if (file_exists($this->upload_file)) { 
                        unlink($this->upload_file);
                    }
                    if (move_uploaded_file($_FILES['xml']['tmp_name'], $this->upload_file)) {
                        $data['log_lines'][] = $this->language->get('log_upload_success'); /*"Файл корректен и был успешно загружен.\n";*/
                        
                        $returnFromValidate = $this->model_tool_sndatatooltransmit->validateXMLmetadata($this->upload_file) ;
                        $data['log_lines'][] = print_r($returnFromValidate,TRUE);
                        if ($returnFromValidate['xml_validation_passed']) {
                            $data['log_lines'][] = $this->language->get('log_validate_xml_structure_1');
                            $data['the_stage'] = 'AFTER_VALIDATION';
                        } else {
                            $data['log_lines'][] = $this->language->get('log_validate_xml_structure_2');
                            $data['the_stage'] = 'DEFAULT';
                        }
                    } else {
                        $data['log_lines'][] = $this->language->get('log_upload_failure'); /*"Возможная атака с помощью файловой загрузки!\n";*/
                    }
                } else {
                    if ($_POST['uploadoption'] == 'server') {
                        $this->upload_file = $_POST['serveraddress'];
                        $data['log_lines'][] = $this->language->get('log_use_from_server');
                    } else {
                        if ($_POST['uploadoption'] == 'other') {
                            $this->upload_file = $_POST['otheraddress'];
                        }
                    }
                }
            } else {
                $data['log_lines'][] = "_POST=".json_encode($_POST);
                $data['log_lines'][] = "upload tmp dir:".sys_get_temp_dir();
                
            }
            //render view template with updated data
            $this->response->setOutput($this->load->view('tool/sndatatooltransmit', $data));
        }
        
}
?>
