<?php

namespace Modules\Admin\Http\Requests;

use App\Http\Requests\Request;
use Input;

class EditorPortfolioRequest  extends Request {

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
                            'image_name'   => 'required|mimes:jpeg,bmp,png,gif',
                            'title' => 'required',
                            'category_name' => 'required',
                            'software_editor' => 'required'

                        ];
                    }
                case 'PUT':
                case 'PATCH': {
                    if ( $editorPortfolio = $this->editorPortfolio) {

                        return [
                            'title' => 'required',
                            'category_name' => 'required',
                            'software_editor' => 'required'
                            
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
