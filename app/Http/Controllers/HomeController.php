<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Article;
use App\Category;
use App\Http\Requests;
//use Illuminate\Http\Request;
use Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\New_;
//use Illuminate\Foundation\Validator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function getlogin()
    {
       if(Auth::guest())
       {
           $categories = Category::all();
           return view('auth.login',compact('categories'));
       }
        else
            return redirect('/');
    }

    public function postlogin()
    {
        $rules = array(
            'email'    => 'required|email',
            'password' => 'required|alphaNum|min:3'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::to('login')
                ->withErrors($validator)// send back all errors to the login form
                ->withInput(Input::except('password'));
        }

        else {

            // create our user data for the authentication
            $userdata = array(
                'email' => Input::get('email'),
                'password' => Input::get('password')
            );
        }

        if (Auth::attempt($userdata)) {

            return redirect('/');

        } else {

            // validation not successful, send back to form
            return Redirect::to('login');

        }
    }

    public function logout()
    {
        $this->middleware('auth');
        Auth::logout();
        return redirect('login');


    }

    public function getregister()
    {
        if(Auth::guest())
        {
            $categories = Category::all();
            return view('auth.register',compact('categories'));
        }
        else
            return redirect('/');
    }

    public function postregister()
    {
        if(Auth::guest())
        {
            $categories = Category::all();
            return view('auth.register',compact('categories'));
        }
        else
            return redirect('/');
    }

    public function index()
    {
        $blogs =  Article::orderBy('id', 'desc')->paginate(5);
        $categories = Category::all();

        return view('blog.index',compact('blogs','categories'));
    }


    //_____________________create post_________________________________


    public function create()
    {
        if($this->middleware('auth'))
        {
            $categories = Category::all();
            return view('blog.create',compact('categories'));
        }
        else
            return view('errors.503');

    }
//----------------------------insert post---------------------------------

    /**
     * @return mixed
     */
    public function post()
    {
        if($this->middleware('auth'))
        {
            if(isset($_POST['postButton']))
            {
                $image = $_FILES['image']['name'];
                $tempName = $_FILES['image']['tmp_name'];

               // $cat = Request::input('category');
                //$category_id= int(Category::select('id')->where('name',$cat));
                //$category_id = DB::table('categories')->where('name', $cat);

                //echo $category_id;
                $Article = new Article();

                $Article->title = Request::input('title');
                $Article->cover = $image;
                $Article->author_id= Request::input('author');
                $Article->category_id = Request::input('category');
                $Article->body = Request::input('body');

                $Article->save();


                if(isset($image))
                {
                    if(!empty($image))
                    {
                        $location = "images/";
                        if(move_uploaded_file($tempName, $location.$image))
                        {
                            echo 'Image Uploaded';

                        }

                    }
                }
            }
            return redirect('/');

        }
        else
            return view('errors.503');

    }

    //_____________________Single post_________________________________

    public function singlepost($id)
    {
        if($this->middleware('auth'))
        {
            $blog=Article::find($id);
            $categories = Category::all();
            return view('blog.singlepost',compact('blog','categories'));
        }
        else
            return view('errors.503');

    }

    //_____________________My blogs_________________________________


    public function myblog()
    {
        if($this->middleware('auth'))
        {
            $id = Auth::user()->id;
            $categories= Category::all();
            $blogs = Article::where('author_id', $id)->orderBy('created_at', 'desc')->paginate(5);
            return view('blog.index',compact('blogs','categories'));
        }
        else
            return view('errors.503');
    }


    //_____________________edit post_________________________________


    public function editpost($id)
    {
        if ($this->middleware('auth'))
        {
            $blog = Article::select()->where('id',$id)->first();
            /*
            $blog = array(
                'title'=>$old->title,
                'cover'=>$old->cover,
                'author_id'=>$old->author_id,
                'category_id'=>$old->category_id,
                'body'=>$old->body,
            );
     */
            $categories = Category::all();
            return view('blog.update',compact('blog','categories'));
        }
        else
            return view('errors.503');
    }


    //_____________________Update post_________________________________

    public function updatepost($id)
    {
        if ($this->middleware('auth'))
        {
            if(isset($_POST['updateButton']))
            {
                $image = $_FILES['image']['name'];
                $tempName = $_FILES['image']['tmp_name'];
                /*
                            $data = array([
                                Request::input('title'),
                                $image,
                                Request::input('author'),
                                Request::input('category'),
                                Request::input('body'),
                            ]);
                */
                $data = Request::all();
                $update_data = Article::find($id);
                $update_data->update($data);
                if(isset($image))
                {
                    if(!empty($image))
                    {
                        $location = "images/";
                        move_uploaded_file($tempName, $location.$image);
                    }
                }
            }
           // $flight = App\Flight::find(1);
            //$flight->name = 'New Flight Name';
            //$flight->save();
            return redirect('../');
        }
        else
            return view('errors.503');

    }

    //_____________________Remove post_________________________________

    public function removepost($id)
    {
        if ($this->middleware('auth'))
        {
            Article::destroy($id);
            return redirect('/');
        }
        else
            return view('errors.503');
    }


    public function showprofile($id)
    {
        $this->middleware('auth');
        $user = User::select()->where('id',$id)->first();
        $categories = Category::all();
        return view('blog.myprofile',compact('user','categories'));
    }

    public function updateprofile($id)
    {
        $this->middleware('auth');
       if(isset($_POST['updateprofile']))
       {
           /*$data = new User();
           $data->name = Request::input('name');
           $data->email = Request::input('email');
           $data->password = Request::input('password');

           $update_data = User::find($id);

            $update_data->name = $data->name;
            $update_data->email = $data->email;
            $update_data->password = $data->password;
           $update_data->save();
           */
           $user = User::where('id',$id)->firstOrFail();
           $user->fill(Request::all());
           $user->save();

           return redirect('/');
       }
        else
            echo "error";



    }


}
