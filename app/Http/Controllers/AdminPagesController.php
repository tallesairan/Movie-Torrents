<?php

namespace App\Http\Controllers;

use App\Actor;
use App\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPagesController extends Controller
{
    //Return all actors order by name
    public function getActors()
    {
        $actors = Actor::orderBy('name','ASC')->get();

        return view('actors')->with('actors',$actors);
    }

    //Add new actor
    public function postActor(Request $request)
    {
        $this->validate($request,[
            'actor'=>'required|min:5|max:30|regex:/^[(a-zA-Z\s)]+$/u',
            'birth_year'=>'required|numeric',
            'actor_image'=>'dimensions:max_width=256,max_height=256|mimes:jpg,jpeg,png'
        ]);

        $actor = Actor::where('name',$request['actor'])->where('birth_year',$request['birth_year'])->first();

        if($actor){
            return redirect()->route('getActors')->with(['fail'=>'Actor '.$actor->name.' is already in database']);
        }

        $actor = new Actor();
        $actor->name = $request['actor'];
        $actor->birth_year = $request['birth_year'];

        if($request->hasFile('actor_image')){
            $actor_image = $request->file('actor_image');
            $filename = time(). '.'. $actor_image->getClientOriginalName();
            $actor_image->move('images/actors/',$filename);

            $actor->thumbnail_path = $filename;
        }

        $actor->save();

        return redirect()->route('getActors')->with(['success'=>'Actor '.$request['actor'].' successfully added']);
    }

    //Delete actor
    public function deleteActor($actor_id)
    {
        $actor = Actor::find($actor_id);

        if(!$actor){
            return redirect()->route('getActors')->with(['fail'=>'That actor is not in database']);
        }

        //delete image with that filename
        $filename = $actor->thumbnail_path;
        unlink(public_path().'/images/actors/'.$filename);

        $actor->delete();

        return redirect()->route('getActors')->with(['success'=>'Actor '.$actor->name.' successfully deleted']);

    }

    //Return all genres order by name
    public function getGenres()
    {
        foreach(Auth::user()->roles as $role) {
            if ($role->name !== 'admin') {
                return redirect()->route('home');
            }
        }
        $genres = Genre::orderBy('name','ASC')->get();

        return view('genres')->with('genres',$genres);
    }

    //Add new genre
    public function postGenre(Request $request)
    {
        $this->validate($request, [
            'genre'=>'required|min:3|max:11|regex:/^[(a-zA-Z\-)]+$/u'
        ]);

        $genre = Genre::where('name',$request['genre'])->first();
        if($genre){
            return redirect()->route('getGenres')->with(['fail'=>'Genre '.$genre->name.' is already in database']);
        }

        $genre = new Genre();
        $genre->name = $request['genre'];
        $genre->save();

        return redirect()->route('getGenres')->with(['success'=>'Genre '.$request["genre"].' successfully added']);
    }

    //Delete genre
    public function deleteGenre($genre_id)
    {
       $genre = Genre::find($genre_id);

        if(!$genre){
            return redirect()->route('getGenres')->with(['fail'=>'That genre s not in database']);
        }

        $genre->delete();
        return redirect()->route('getGenres')->with(['success'=>'Genre '.$genre->name.' successfully deleted']);
    }

    public function postEditGenre(Request $request, $genre_id)
    {
        $this->validate($request, [
            'genre'=>'required|min:3|max:11|regex:/^[(a-zA-Z\-)]+$/u'
        ]);

        $genre = Genre::find($genre_id);

        if(!$genre){
            return redirect()->route('getGenres')->with(['fail'=>'Genre '.$request['genre'].' is not in database']);
        }

        $genre->name = $request['genre'];
        $genre->save();

        return redirect()->route('getGenres')->with(['success'=>'Genre '.$request['genre'].' successfully edited']);
    }
}
