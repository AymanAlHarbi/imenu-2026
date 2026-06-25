<?php

namespace Modules\Whatsi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class Main extends Controller
{
    public function setAPI()
    {
        $vendor = auth()->user()->restaurant;
        if($vendor){
            return view('whatsi::set_api',['api_key'=>$vendor->getConfig('whatsi_api','')]);
        }
    }
    public function set_campaings()
    {


        $vendor = auth()->user()->restaurant;
        if($vendor&&$vendor->getConfig('whatsi_api','')==""){
            return $this->setAPI();
        }

        $api_key = $vendor->getConfig('whatsi_api','');
        

        //If we have selected_ID, then save it
        if(request()->input('selected_id')){
            $vendor->setConfig('whatsi_status_change_campaigns',request()->input('selected_id'));
        }

       
       

        try {
             //get the campaigns
            $campaigns = $this->getAPICampaigns($api_key);

            if(isset($campaigns['status'])&&$campaigns['status']=='success'){
                $campaignItems = $campaigns['items'];
                
                //If more than 0 campaigns, then show the campaigns
                if(count($campaignItems)>0){
                    //Get the names and ids of the campaigns
                    $campaignsData = [];
                    foreach($campaignItems as $campaign){
                        $campaignsData[$campaign['id']] = $campaign['name'];
                    }
               
                    $selected_campaign = $vendor->getConfig('whatsi_status_change_campaigns',"");
                    return view('whatsi::campaings',['campaigns'=>$campaignsData,'selected_campaign'=>$selected_campaign]);
                }else{
                    return view('whatsi::set_api',['error'=>'No campaigns found','api_key'=>$api_key]);
                }
            }
        } catch (\Exception $e) {
            return view('whatsi::set_api',['error'=>'Error in getting the campaigns','api_key'=>$api_key]);
        }

        return view('whatsi::set_api',['error'=>'Error in getting the campaigns. Make sure your API Key is correct.','api_key'=>$api_key]);
    }

    //Get API Campaigns
    public function getAPICampaigns($api_key)
    {
        $token = $api_key;
        $url = config('whatsi.url');
        $url = rtrim($url, '/');
        $url = $url . '/api/wpbox/getCampaigns?token='.$token."&type=api";
       ;
        //get the campaigns
        $response = \Http::get($url);
        $campaigns = $response->json();
        return $campaigns;
    }

   

    public function store_api_key(Request $request)
    {
        $api_key = $request->input('api_key');
        auth()->user()->restaurant->setConfig('whatsi_api',$api_key);
        return redirect()->route('whatsi.campaings');
    }


}
