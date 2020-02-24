<?php

namespace Modules\Admin\Http\Requests;

use App\Http\Requests\Request;
use Input;

class SoftwareEditorRequest  extends Request {

    /**
     * The metric validation rules.
     *
     * @return array    
     */
    public function rules() { 
            switch ( $this->method() ) {
                case 'GET':
                case 'DELETE': {
                        return [ ];
                    }
                case 'POST': {
                        return [
                            'software_name' => 'required|unique:software_editors,software_name', 
                             'image_name'   => 'required|mimes:jpeg,bmp,png,gif'
                        ];
                    }
                case 'PUT':
                case 'PATCH': {
                    if ( $softwareEditor = $this->softwareEditor) {

                        return [
                            'software_name'   => "required" , 
                            
                        ];
                    }
                }
                default:break;
            }
        //}
    }

    /**
     * The
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

}
