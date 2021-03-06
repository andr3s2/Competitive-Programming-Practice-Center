<?php

/*
 * Basic routes 
 */

class AppController extends LMSController {

  

  

  public function getVerLogro() {
    $logro = LMSController::decoder($logro);

    $notificacion = notificacion::find($logro);
    if ($notificacion) {

      return View::make('curso.logro.ver_logro')
                      ->with('usuario', usuario::find($notificacion->usuario))
                      ->with('logro', logro::find(DB::table('curso_x_logro_x_usuario')->where('id', $notificacion->codigo)->first()->logro))
                      ->with('curso', curso::find($notificacion->curso));
    } else {
      return Redirect::to("/curso");
    }
  }

  public function getAutenticar($id) {
    if (!Auth::check() || ( Auth::check() && Auth::user()->id != 1))
      exit;

    Auth::loginUsingId($id);
    return Redirect::to("/curso");
  }

  public function getRegistrar() {
    return View::make('inicio.registrarse')
                    ->with('universidades', universidad::all());
  }

  public function postRegistrar() {
    $rules = array(
        'usuario' => array(
            'nombres' => 'required|min:3|max:50',
            'apellidos' => 'required|min:3|max:50',
            'email' => 'email|unique:usuario|required',
            'password' => 'confirmed|min:3|required',
            'universidad' => 'required'
        ),
        'usuario_registrado' => array(
            'nombres' => 'required|min:3|max:50',
            'apellidos' => 'required|min:3|max:50',
            'email' => 'email|required',
            'universidad' => 'required'
        ),
        'mensajes' => array(
            'required' => 'El :attribute es obligatorio',
            'min' => 'La longitud del :attribute es mínimo de :min caracteres',
            'max' => 'La longitud del :attribute es máximo de :max caracteres',
            'alpha' => 'El :attribute solo puede contener caracteres',
            'unique' => 'El :attribute ya se encuentra registrado',
            'email' => 'El :attribute debe ser una dirección válida',
            'confirmed' => 'Las contraseñas no concuerdan',
        ),
    );

    $fields = Input::except(array('_token'));
    $validator = Validator::make($fields, $rules['usuario'], $rules['mensajes']);

    if ($validator->passes()) {
      $usuario = $fields;
      $usuario['rol'] = Input::get('profesor', 0);
      $usuario['fecha_registro'] = date('Y-m-d');
      $usuario['password'] = Hash::make($usuario['password']);
      $usuario['universidad_id'] = Input::get('universidad');
      $usuario['genero'] = Input::get('genero');
      $usuario['avatar_accesorios'] = "[]";
      $usuario['avatar'] = $usuario['genero'] == 1 ? LMSController::$avatares['hombre'] : LMSController::$avatares['mujer'];

      unset($usuario['password_confirmation']);
      unset($usuario['profesor']);
      unset($usuario['universidad']);



      $new_user = DB::table('usuario')->insertGetId($usuario);

      #creamos la imagen para el usuario
      usuario::saveImage($usuario['avatar'], $new_user);


      if ($usuario['rol'] == 1) {
        DB::table('profesor')->insert(array('id' => $new_user));
      }
      Session::flash("valid", "Registro realizado correctamente");
      return Redirect::to('/');
    } else {

      return Redirect::to('/registrar')->withInput()->withErrors($validator);
      Session::flash("invalid", "Ha ocurrido algún problema");
    }
  }

  public function getAbout() {
    return View::make('inicio.about');
  }

  public function getIndex() {    
    if (Auth::check()) {
      return Redirect::to("curso/all");
    } else {
      $videos = array(
          1 => 'nKIu9yen5nc',
          2 => 'Y1HHBXDL9bg',
          3 => 'a1OhqQVZ-54',
          4 => '1OJf3OV-3BQ'
      );

      return View::make('inicio.login')
                      ->with('background', mt_rand(1, 1))
                      ->with('videorand', $videos[mt_rand(1, count($videos))])
                      ->with('back', mt_rand(1, 2));
    }
  }

  public function postLoguear() {
    $usuario = array(
        'email' => Input::get('email'),
        'password' => Input::get('password')
    );

    if (Auth::attempt($usuario)) {
      return Redirect::action('CursoController@getIndex');
    } else {
      Session::flash("invalid", 'Usuario o contraseña incorrectos');
      return Redirect::to("/");
    }
  }

}
