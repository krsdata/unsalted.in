 
  
                                        <div class="form-body">
                                            <div class="alert alert-danger display-hide">
                                                <button class="close" data-close="alert"></button> Please fill the required field! </div>
                                           
										 	<div class="form-group {{ $errors->first('title', ' has-error') }}  @if(session('field_errors')) {{ 'has-error' }} @endif">
										        <label class="control-label col-md-3">  Title <span class="required"> * </span></label>
										        <div class="col-md-4"> 
										            {!! Form::text('title',null, ['class' => 'form-control','data-required'=>1])  !!} 
										            
										            <span class="help-block" style="color:red">{{ $errors->first('title', ':message') }} @if(session('field_errors')) {{ 'The  Title name already been taken!' }} @endif</span>
										        </div>
										    </div>  

                            <div class="form-group {{ $errors->has('parent_id') ? 'has-error' : '' }}">
                                     <label class="control-label col-md-3">  Sub Menu <span class="required"> * </span></label>
                            <div class="col-md-4"> 
                                    {!! Form::select('parent_id',$allMenu, old('parent_id'), ['class'=>'form-control', 'placeholder'=>'Select Menu']) !!}
                                    <span class="text-danger">{{ $errors->first('parent_id') }}</span>
                                </div>
                            </div>    
                                        <div class="form-actions">
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-9">
                                                  {!! Form::submit(' Save ', ['class'=>'btn  btn-primary text-white','id'=>'saveBtn']) !!}


                                                   <a href="{{route('category')}}">
            {!! Form::button('Back', ['class'=>'btn btn-warning text-white']) !!} </a>
                                                </div>
                                            </div>
                                        </div>




    <div class="form-body">

 



</div> 

