<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;

return function (App $app) {
    $container = $app->getContainer();

    //memperbolehkan cors origin 
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });
    $app->add(function ($req, $res, $next) {
        $response = $next($req, $res);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, token')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });

    $app->post('/login', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $Username=trim(strip_tags($input['Username']));
        $Password=trim(strip_tags($input['Password']));
        $sql = "SELECT IdUser, Username  FROM `user` WHERE Username=:Username AND `Password`=:Password";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("Username", $Username);
        $sth->bindParam("Password", $Password);
        $sth->execute();
        $user = $sth->fetchObject();       
        if(!$user) {
            return $this->response->withJson(['status' => 'error', 'message' => 'These credentials do not match our records username.'],200);  
        }
        $settings = $this->get('settings');       
        $token = array(
            'IdUser' =>  $user->IdUser, 
            'Username' => $user->Username
        );
        $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
        return $this->response->withJson(['status' => 'success','data'=>$user, 'token' => $token],200); 
    });

    $app->post('/register', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $Username=trim(strip_tags($input['Username']));
        $NamaLengkap=trim(strip_tags($input['NamaLengkap']));
        $Email=trim(strip_tags($input['Email']));
        $Password=trim(strip_tags($input['Password']));
        $sql = "INSERT INTO user(Username, NamaLengkap, Email, Password) 
                VALUES(:Username, :NamaLengkap, :Email, :Password)";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("Username", $Username);
        $sth->bindParam("NamaLengkap", $NamaLengkap);
        $sth->bindParam("Email", $Email);
        $sth->bindParam("Password", $Password); 
        $StatusInsert=$sth->execute();
        if($StatusInsert){
            $IdUser=$this->db->lastInsertId();     
            $settings = $this->get('settings'); 
            $token = array(
                'IdUser' =>  $IdUser, 
                'Username' => $Username
            );
            $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
            $dataUser=array(
                'IdUser'=> $IdUser,
                'Username'=> $Username
                );
            return $this->response->withJson(['status' => 'success','data'=>$dataUser, 'token'=>$token],200); 
        } else {
            return $this->response->withJson(['status' => 'error','data'=>'error insert user.'],200); 
        }
    });

    $app->group('/api', function(\Slim\App $app) {
        //letak rute yang akan kita autentikasi dengan token

        //ambil data user berdasarkan id user
        $app->get("/user/{IdUser}", function (Request $request, Response $response, array $args){
            $IdUser = trim(strip_tags($args["IdUser"]));
            $sql = "SELECT IdUser, Username, NamaLengkap, Email FROM `user` WHERE IdUser=:IdUser";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("IdUser", $IdUser);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $result = $stmt->fetchObject();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'no result data.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
        });

        //ARTIST
        $app->get("/artist/", function (Request $request, Response $response){
            $sql = "SELECT * FROM artist";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/artist/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM artist WHERE id_artist=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });
    

        $app->get("/artist/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM artist WHERE artist_name LIKE '%$keyword%' OR origin LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/artist/", function (Request $request, Response $response){
            $new_artist = $request->getParsedBody();
            $sql = "INSERT INTO artist (artist_name, origin) VALUE (:artist_name, 
                    :origin)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":artist_name" => $new_artist["artist_name"],
            ":origin" => $new_artist["origin"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });  
        
        $app->put("/artist/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_artist = $request->getParsedBody();
            $sql = "UPDATE artist SET artist_name=:artist_name, origin=:origin WHERE id_artist=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":artist_name" => $new_artist["artist_name"],
                ":origin" => $new_artist["origin"]
                ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }); 
        
        $app->delete("/artist/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM artist WHERE id_artist=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });


        //GENRE
        $app->get("/genre/", function (Request $request, Response $response){
            $sql = "SELECT * FROM genre";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/genre/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM genre WHERE id_genre=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/genre/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM genre WHERE genre_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/genre/", function (Request $request, Response $response){
            $new_genre = $request->getParsedBody();
            $sql = "INSERT INTO genre (genre_name) VALUE (:genre_name)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":genre_name" => $new_genre["genre_name"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });


        $app->put("/genre/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_genre = $request->getParsedBody();
            $sql = "UPDATE genre SET genre_name=:genre_name WHERE id_genre=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":genre_name" => $new_genre["genre_name"]
                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });
        
        $app->delete("/genre/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM genre WHERE id_genre=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //LABEL
        $app->get("/label/", function (Request $request, Response $response){
            $sql = "SELECT * FROM label";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/label/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM label WHERE id_label=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/label/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM label WHERE label_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/label/", function (Request $request, Response $response){
            $new_label = $request->getParsedBody();
            $sql = "INSERT INTO label (label_name) VALUE (:label_name)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":label_name" => $new_label["label_name"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->put("/label/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_label = $request->getParsedBody();
            $sql = "UPDATE label SET label_name=:label_name WHERE id_label=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":label_name" => $new_label["label_name"]                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->delete("/label/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM label WHERE id_label=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //PRODUCER
        $app->get("/producer/", function (Request $request, Response $response){
            $sql = "SELECT * FROM producer";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/producer/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM producer WHERE id_producer=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });
    

        $app->get("/producer/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM producer WHERE producer_namer LIKE '%$keyword%' OR award LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/producer/", function (Request $request, Response $response){
            $new_producer = $request->getParsedBody();
            $sql = "INSERT INTO producer (producer_namer, award) VALUE (:producer_namer, 
                    :award)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":producer_namer" => $new_producer["producer_namer"],
            ":award" => $new_producer["award"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });  
        
        $app->put("/producer/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_producer = $request->getParsedBody();
            $sql = "UPDATE producer SET producer_namer=:producer_namer, award=:award WHERE id_producer=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":producer_namer" => $new_producer["producer_namer"],
                ":award" => $new_producer["award"]
                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }); 
        
        $app->delete("/producer/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM producer WHERE id_producer=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //STUDIO
        $app->get("/studio/", function (Request $request, Response $response){
            $sql = "SELECT * FROM studio";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/studio/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM studio WHERE id_studio=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/studio/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM studio WHERE studio_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/studio/", function (Request $request, Response $response){
            $new_studio = $request->getParsedBody();
            $sql = "INSERT INTO studio (studio_name) VALUE (:studio_name)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":studio_name" => $new_studio["studio_name"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->put("/studio/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_studio = $request->getParsedBody();
            $sql = "UPDATE studio SET studio_name=:studio_name WHERE id_studio=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":studio_name" => $new_studio["studio_name"]                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->delete("/studio/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM studio WHERE id_studio=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //CHART
        $app->get("/chart/", function (Request $request, Response $response){
            $sql = "SELECT * FROM chart";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/chart/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM chart WHERE id_chart=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/chart/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM chart WHERE chart_name LIKE '%$keyword%' OR year LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/chart/", function (Request $request, Response $response){
            $new_chart = $request->getParsedBody();
            $sql = "INSERT INTO chart (chart_name, year) VALUE (:chart_name, :year)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":chart_name" => $new_chart["chart_name"],
            ":year" => $new_chart["year"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->put("/chart/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_chart = $request->getParsedBody();
            $sql = "UPDATE chart SET chart_name=:chart_name, year=:year WHERE id_chart=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":chart_name" => $new_chart["chart_name"],
                ":year" => $new_chart["year"]
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }); 

        $app->delete("/chart/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM chart WHERE id_chart=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //LICENCES
        $app->get("/licensing/", function (Request $request, Response $response){
            $sql = "SELECT * FROM licensing";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/licensing/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM licensing WHERE id_licensing=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });
    

        $app->get("/licensing/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM licensing WHERE license_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/licensing/", function (Request $request, Response $response){
            $new_licensing = $request->getParsedBody();
            $sql = "INSERT INTO licensing (license_name) VALUE (:license_name)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":license_name" => $new_licensing["license_name"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });  
        
        $app->put("/licensing/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_licensing = $request->getParsedBody();
            $sql = "UPDATE licensing SET license_name=:license_name WHERE id_licensing=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":license_name" => $new_licensing["license_name"]
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }); 
        
        $app->delete("/licensing/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM licensing WHERE id_licensing=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //Music_publisher
        $app->get("/music_publisher/", function (Request $request, Response $response){
            $sql = "SELECT * FROM music_publisher";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/music_publisher/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT * FROM music_publisher WHERE id_publisher=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });
    

        $app->get("/music_publisher/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM music_publisher WHERE publisher_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/music_publisher/", function (Request $request, Response $response){
            $new_publisher = $request->getParsedBody();
            $sql = "INSERT INTO music_publisher (publisher_name) VALUE (:publisher_name)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":publisher_name" => $new_publisher["publisher_name"]
            
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });  

        $app->put("/music_publisher/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_publisher = $request->getParsedBody();
            $sql = "UPDATE music_publisher SET publisher_name=:publisher_name WHERE id_publisher=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":publisher_name" => $new_publisher["publisher_name"]                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }); 
        
        $app->delete("/music_publisher/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM music_publisher WHERE id_publisher=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        
        //SONGS
        $app->get("/songs/", function (Request $request, Response $response){
            $sql = "SELECT id_song, title, plays, genre_name, album_name, artist_name, label_name FROM songs 
            JOIN genre USING(id_genre)
            JOIN album USING(id_album)
            INNER JOIN artist as a on (a.id_artist=songs.id_artist)
            INNER JOIN label  as l on(l.id_label = songs.id_label)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/songs/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT id_song, title, plays, genre_name, album_name, artist_name, label_name
            FROM songs 
            JOIN genre USING(id_genre)
            JOIN album USING(id_album)
            INNER JOIN artist as a on (a.id_artist=songs.id_artist)
            INNER JOIN label  as l on(l.id_label = songs.id_label) WHERE id_song=:id ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/songs/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT id_song, title, plays, genre_name, album_name, a.artist_name, l.label_name
            FROM songs 
            JOIN genre USING(id_genre)
            JOIN album USING(id_album)
            INNER JOIN artist as a on (a.id_artist=songs.id_artist)
            INNER JOIN label  as l on(l.id_label = songs.id_label)
            WHERE title LIKE '%$keyword%' OR album_name LIKE '%$keyword%' OR  a.artist_name LIKE '%$keyword%' OR  l.label_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });
        
        $app->post("/songs/", function (Request $request, Response $response){
            $new_songs = $request->getParsedBody();
            $sql = "INSERT INTO songs (title, plays, id_genre, id_album, id_artist, id_label) VALUE (:title, :plays, :id_genre, :id_album, :id_artist, :id_label)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":title" => $new_songs["title"],
            ":plays" => $new_songs["plays"],
            ":id_genre" => $new_songs["id_genre"],
            ":id_album" => $new_songs["id_album"],
            ":id_artist" => $new_songs["id_artist"],
            ":id_label" => $new_songs["id_label"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->put("/songs/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_songs = $request->getParsedBody();
            $sql = "UPDATE songs SET title=:title, plays=:plays, id_genre=:id_genre, id_album=:id_album, id_artist=:id_artist, id_label=:id_label WHERE id_song=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":title" => $new_songs["title"],
                ":plays" => $new_songs["plays"],
                ":id_genre" => $new_songs["id_genre"],
                ":id_album" => $new_songs["id_album"],
                ":id_artist" => $new_songs["id_artist"],
                ":id_label" => $new_songs["id_label"]
                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->delete("/songs/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM songs WHERE id_song=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        //ALBUM
        $app->get("/album/", function (Request $request, Response $response){
            $sql = "SELECT id_album, album_name, realease_date, genre_name, label_name, chart_name, producer_namer, publisher_name, license_name, artist_name, studio_name
            FROM album 
            JOIN genre USING(id_genre)
            JOIN label USING(id_label)
            JOIN chart USING(id_chart)
            JOIN producer USING(id_producer)
            JOIN music_publisher USING(id_publisher)
            JOIN licensing USING(id_licensing)
            JOIN artist USING(id_artist)
            JOIN studio USING(id_studio)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 200);
        });

        $app->get("/album/{id}", function (Request $request, Response $response, 
        $args){
            $id = $args["id"];
            $sql = "SELECT id_album, album_name, realease_date, genre_name, label_name, chart_name, producer_namer, publisher_name, license_name, artist_name, studio_name
            FROM album 
            JOIN genre USING(id_genre)
            JOIN label USING(id_label)
            JOIN chart USING(id_chart)
            JOIN producer USING(id_producer)
            JOIN music_publisher USING(id_publisher)
            JOIN licensing USING(id_licensing)
            JOIN artist USING(id_artist)
            JOIN studio USING(id_studio) WHERE id_album=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $result = $stmt->fetch();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });


        $app->get("/album/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT id_album, album_name, realease_date, genre_name, label_name, chart_name, producer_namer, publisher_name, license_name, artist_name, studio_name
            FROM album 
            JOIN genre USING(id_genre)
            JOIN label USING(id_label)
            JOIN chart USING(id_chart)
            JOIN producer USING(id_producer)
            JOIN music_publisher USING(id_publisher)
            JOIN licensing USING(id_licensing)
            JOIN artist USING(id_artist)
            JOIN studio USING(id_studio) 
            WHERE album_name LIKE '%$keyword%' OR realease_date LIKE '%$keyword%' OR genre_name LIKE '%$keyword%' OR chart_name LIKE '%$keyword%' OR producer_namer LIKE '%$keyword%' OR license_name LIKE '%$keyword%' OR artist_name LIKE '%$keyword%' OR studio_name LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->post("/album/", function (Request $request, Response $response){
            $new_album = $request->getParsedBody();
            $sql = "INSERT INTO album (album_name, realease_date, id_genre, id_label, id_chart, id_producer, id_publisher, id_licensing, id_artist, id_studio) VALUE (:album_name, :realease_date, :id_genre, :id_label, :id_chart, :id_producer, :id_publisher, :id_licensing, :id_artist, :id_studio)";
            $stmt = $this->db->prepare($sql);
            $data = [
            ":album_name" => $new_album["album_name"],
            ":realease_date" => $new_album["realease_date"],
            ":id_genre" => $new_album["id_genre"],
            ":id_label" => $new_album["id_label"],
            ":id_chart" => $new_album["id_chart"],
            ":id_producer" => $new_album["id_producer"],
            ":id_publisher" => $new_album["id_publisher"],
            ":id_licensing" => $new_album["id_licensing"],
            ":id_artist" => $new_album["id_artist"],
            ":id_studio" => $new_album["id_studio"]
            ];
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 
        200);
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->put("/album/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_album = $request->getParsedBody();
            $sql = "UPDATE album SET album_name=:album_name, realease_date=:realease_date, id_genre=:id_genre, id_label=:id_label, id_chart=:id_chart, id_producer=:id_producer, id_publisher=:id_publisher, id_licensing=:id_licensing, id_artist=:id_artist, id_studio=:id_studio WHERE id_album=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id,
                ":album_name" => $new_album["album_name"],
                ":realease_date" => $new_album["realease_date"],
                ":id_genre" => $new_album["id_genre"],
                ":id_label" => $new_album["id_label"],
                ":id_chart" => $new_album["id_chart"],
                ":id_producer" => $new_album["id_producer"],
                ":id_publisher" => $new_album["id_publisher"],
                ":id_licensing" => $new_album["id_licensing"],
                ":id_artist" => $new_album["id_artist"],
                ":id_studio" => $new_album["id_studio"]
                
            ];
            if ($stmt->execute($data))
                return $response->withJson(["status" => "Success", "data" => "1"], 200);

            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });

        $app->delete("/album/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM album WHERE id_album=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data))
            return $response->withJson(["status" => "success", "data" => "1"], 200);
            
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        });
    });
};
