<?php

// Klassendefinition
class Wetterstationsautomatik_v2 extends IPSModule
{
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    * ABC_MeineErsteEigeneFunktion($id);
    *
    */
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        parent::Create();
            
        // Profile
        if (!IPS_VariableProfileExists("BESCHATTUNG.Switch")) {
            IPS_CreateVariableProfile("BESCHATTUNG.Switch", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.Switch", "Power");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", false, $this->Translate("Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", true, $this->Translate("On"), "", 0x3ADF00);
        }
                
        if (!IPS_VariableProfileExists("BESCHATTUNG.SwitchSonne")) {
            IPS_CreateVariableProfile("BESCHATTUNG.SwitchSonne", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.SwitchSonne", "Sun");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchSonne", false, $this->Translate("Beschattung-Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchSonne", true, $this->Translate("Beschattung-On"), "", 0x3ADF00);
        }
            
        if (!IPS_VariableProfileExists("BESCHATTUNG.SwitchAlarm")) {
            IPS_CreateVariableProfile("BESCHATTUNG.SwitchAlarm", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.SwitchAlarm", "Alert");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchAlarm", false, $this->Translate("Alarm-Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchAlarm", true, $this->Translate("Alarm-On"), "", 0xDF0101);
        }
                
        if (!IPS_VariableProfileExists("SchwellwertWind")) {
            IPS_CreateVariableProfile("SchwellwertWind", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertWind", "WindSpeed");
            IPS_SetVariableProfileText("SchwellwertWind", "", " km/h");
        }
                
        if (!IPS_VariableProfileExists("SchwellwertSonne")) {
            IPS_CreateVariableProfile("SchwellwertSonne", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertSonne", "Sun");
            IPS_SetVariableProfileText("SchwellwertSonne", "", " lx");
        }

        if (!IPS_VariableProfileExists("SchwellwertAzimut")) {
            IPS_CreateVariableProfile("SchwellwertAzimut", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertAzimut", "WindDirection");
            IPS_SetVariableProfileText("SchwellwertAzimut", "", "°");
        }
            
        // Variablen für die Beschattung
        $this->RegisterVariableBoolean("Status", "Beschattungsautomatik Aktiv", "BESCHATTUNG.Switch", 1);
        $this->EnableAction("Status");
        $this->RegisterVariableBoolean("BeschattungWiederholen", "Nach Windalarm Beschattung erneut prüfen", "BESCHATTUNG.Switch", 11);
        $this->EnableAction("BeschattungWiederholen");
        $this->RegisterVariableBoolean("BeschattungWiederholen2", "Nach Regen Beschattung erneut prüfen", "BESCHATTUNG.Switch", 13);
        $this->EnableAction("BeschattungWiederholen2");

        $this->RegisterVariableString("LuxSollOben", "Helligkeit: Oberen Schwellwert", "SchwellwertSonne", 2);
        $this->EnableAction("LuxSollOben");
        $this->RegisterVariableString("LuxSollUnten", "Helligkeit: Unteren Schwellwert", "SchwellwertSonne", 3);
        $this->EnableAction("LuxSollUnten");
        $this->RegisterVariableString("AzimutSollVon", "Azimut: Von", "SchwellwertAzimut", 5);
        $this->EnableAction("AzimutSollVon");
        $this->RegisterVariableString("AzimutSollBis", "Azimut: Bis", "SchwellwertAzimut", 6);
        $this->EnableAction("AzimutSollBis");
        $this->RegisterVariableFloat("CurrentAzimut", "Aktueller Azimut", "~SunAzimuth.F", 7);
        $this->RegisterVariableBoolean("Beschattungsstatus", "Beschattung aktiv", "BESCHATTUNG.SwitchSonne", 4);
            
        // Variablen für Wind
        $this->RegisterVariableString("WindSollOben", "Wind: Oberen Schwellwert", "SchwellwertWind", 9);
        $this->EnableAction("WindSollOben");
        $this->RegisterVariableString("WindSollUnten", "Wind: Unteren Schwellwert", "SchwellwertWind", 10);
        $this->EnableAction("WindSollUnten");
        $this->RegisterVariableBoolean("Windstatus", "Wind: Alarm", "BESCHATTUNG.SwitchAlarm", 8);
            
        // Variable für Regen
        $this->RegisterVariableBoolean("Regenstatus", "Regen: Alarm", "BESCHATTUNG.SwitchAlarm", 12);
             
        // Eigenschaften speichern
        $this->RegisterPropertyInteger("Helligkeit", 0);
        $this->RegisterPropertyInteger("Azimut", 0);
        $this->RegisterPropertyInteger("Regensensor", 0);
        $this->RegisterPropertyInteger("Windsensor", 0);

        // Register message
        $this->RegisterMessage($this->GetIDForIdent("Status"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("BeschattungWiederholen"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("BeschattungWiederholen2"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("LuxSollOben"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("LuxSollUnten"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("AzimutSollVon"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("AzimutSollBis"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("CurrentAzimut"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("Beschattungsstatus"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("WindSollOben"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("WindSollUnten"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("Windstatus"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->GetIDForIdent("Regenstatus"), 10603 /* VM_UPDATE */);

        $this->SetVisualizationType(1);
    }

    public function RequestAction($Ident, $Value) {
        // $this->SendDebug('HTML Update', 'Start', 0);
        $this->SetValue($Ident, $Value);

        // $this->UpdateVisualizationValue($this->GetUpdatedValue($Ident));
        // $this->SendDebug('HTML Update', 'Done', 0);
    }

    public function ApplyChanges() {
        parent::ApplyChanges();
        
        $this->RegisterMessage($this->ReadPropertyInteger("Helligkeit"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->ReadPropertyInteger("Azimut"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->ReadPropertyInteger("Windsensor"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->ReadPropertyInteger("Regensensor"), 10603 /* VM_UPDATE */);
    }
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        $Helligkeit = $this->ReadPropertyInteger("Helligkeit");
        $Azimut = $this->ReadPropertyInteger("Azimut");
        $Windsensor = $this->ReadPropertyInteger("Windsensor");
        $Regensensor = $this->ReadPropertyInteger("Regensensor");
        $this->SendDebug('HTML Update', 'Start', 0);

        switch ($SenderID) {
            case $Helligkeit:
            case $Azimut:
                $this->BeschattungAktivieren(); // Wird in beiden fällen ausgelöst, Aktualisierung von $Helligkeit und $Azimut
                SetValue($this->GetIDForIdent("CurrentAzimut"), GetValue($this->ReadPropertyInteger("Azimut")));
            break;
            case $Windsensor:
                $this->Windalarm();
            break;
            case $Regensensor:
                $this->Regenalarm();
            break;
            default:
                $ident = IPS_GetObject($SenderID)['ObjectIdent'];
                $this->UpdateVisualizationValue($this->GetUpdatedValue($ident));
        }
        $this->SendDebug('HTML Update', 'Done', 0);
    }

    public function BeschattungAktivieren() {
        $Status = GetValue($this->GetIDForIdent("Status"));     // gibt an ob Beschattungsautomatik aktiv oder inaktiv ist
        $HelligkeitWert = GetValue($this->ReadPropertyInteger("Helligkeit"));
        $LuxSollOben = GetValue($this->GetIDForIdent("LuxSollOben"));
        $LuxSollUnten = GetValue($this->GetIDForIdent("LuxSollUnten"));
        $AzimutWert = GetValue($this->ReadPropertyInteger("Azimut"));
        $AzimutSollVon = GetValue($this->GetIDForIdent("AzimutSollVon"));
        $AzimutSollBis = GetValue($this->GetIDForIdent("AzimutSollBis"));
        $Regenalarm = GetValue($this->GetIDForIdent("Regenstatus"));
        $Windalarm = GetValue($this->GetIDForIdent("Windstatus"));
        $Beschattungsstatus = GetValue($this->GetIDForIdent("Beschattungsstatus"));

        if ($Status) { // Beschattungsautomatik aktiv
            if ($Beschattungsstatus) { // Beschattung ist bereits aktiv
                $BeschattungDeaktivieren = false;
                if($HelligkeitWert <= $LuxSollUnten){
                    $BeschattungDeaktivieren = true;
                }
                if($AzimutWert != false && ($AzimutWert > $AzimutSollBis || $AzimutWert < $AzimutSollVon)){
                    $BeschattungDeaktivieren = true;
                }
                if($BeschattungDeaktivieren){
                    SetValue($this->GetIDForIdent("Beschattungsstatus"), false);
                }
            } else {
                if ($HelligkeitWert >= $LuxSollOben && !$Regenalarm && !$Windalarm) {
                    if($AzimutWert == false || ($AzimutWert >= $AzimutSollVon && $AzimutWert <= $AzimutSollBis)){
                        SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
                    }
                }
            }
        }
    }
        
    public function BeschattungWiederholen() {
        $Status = GetValue($this->GetIDForIdent("Status"));
        $Helligkeit = GetValue($this->ReadPropertyInteger("Helligkeit"));
        $LuxSollOben = GetValue($this->GetIDForIdent("LuxSollOben"));
        $Azimut = GetValue($this->ReadPropertyInteger("Azimut"));
        $AzimutSollVon = GetValue($this->GetIDForIdent("AzimutSollVon"));
        $AzimutSollBis = GetValue($this->GetIDForIdent("AzimutSollBis"));
        $Regen = GetValue($this->GetIDForIdent("Regenstatus"));
        $Wind = GetValue($this->GetIDForIdent("Windstatus"));
           
        if($Status) {
            if ($Azimut >= $AzimutSollVon && $Azimut <= $AzimutSollBis && $Helligkeit >= $LuxSollOben && !$Regen && !$Wind) {
                SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
            }
        }
    }
       
    public function Windalarm() {
        $Windsensor = $this->ReadPropertyInteger("Windsensor");
        $WindsensorWert = GetValue($Windsensor);
        $WindSollOben = GetValue($this->GetIDForIdent("WindSollOben"));
        $WindSollUnten = GetValue($this->GetIDForIdent("WindSollUnten"));
        $Beschattung = GetValue($this->GetIDForIdent("BeschattungWiederholen"));
        
        if ($Windsensor) {
            if ($WindsensorWert >= $WindSollOben) {
                SetValue($this->GetIDForIdent("Windstatus"), true);
				if($Beschattung) {
						SetValue($this->GetIDForIdent("Beschattungsstatus"), false);
				}	
            } elseif ($WindsensorWert <= $WindSollUnten) {
                SetValue($this->GetIDForIdent("Windstatus"), false);
			}
        }
    }
       
    public function Regenalarm() {
        $Regensensor = $this->ReadPropertyInteger("Regensensor");
        $RegensensorWert = GetValue($Regensensor);
        $Beschattung = GetValue($this->GetIDForIdent("BeschattungWiederholen2"));
           
        if ($Regensensor) {
            if ($RegensensorWert) {
                SetValue($this->GetIDForIdent("Regenstatus"), true);
				if($Beschattung) {
						SetValue($this->GetIDForIdent("Beschattungsstatus"), false);
				}
            } else {
                SetValue($this->GetIDForIdent("Regenstatus"), false);
			} 
		}
    }

    public function GetVisualizationTile() {
		// Inject current values using the message handling function
		$initialHandling = [];
		foreach (IPS_GetChildrenIDs($this->InstanceID) as $variableID) {
			if (!IPS_VariableExists($variableID)) {
				continue;
			}

			$ident = IPS_GetObject($variableID)['ObjectIdent'];
			if (!$ident) {
				continue;
			}
            $initialHandling[] = 'handleMessage(\'' . $this->GetUpdatedValue($ident) . '\');';
		}
		$messages = '<script>' . implode(' ', $initialHandling) . '</script>';

        // We need to include the assets directly as there is no way to load anything afterwards yet
        $assets = '<script>';
        $assets .= 'window.assets = {};' . PHP_EOL;
        $assets .= 'window.assets.img_save = "data:image/webp;base64,' . base64_encode(file_get_contents(__DIR__ . '/assets/save.png')) . '";' . PHP_EOL;
        $assets .= 'window.assets.img_edit = "data:image/webp;base64,' . base64_encode(file_get_contents(__DIR__ . '/assets/edit.png')) . '";' . PHP_EOL;
        $assets .= '</script>';

		// Add static HTML content from file to make editing easier
		$module = file_get_contents(__DIR__ . '/module.html');

		// Return everything to render our fancy tile!
		return $module . $assets . $messages;
	}

    private function GetUpdatedValue($variableIdent) {
        $variableID = @$this->GetIDForIdent($variableIdent);

        $variableInfo = IPS_GetVariable($variableID);
        $variableName = IPS_GetName($variableID);
        $variableProfileInfo = IPS_GetVariableProfile($variableInfo["VariableProfile"]);
        // $variableCustomProfileInfo = IPS_GetVariableProfile($variableInfo["VariableCustomProfile"]);

        if (($variableID === false) || (!IPS_VariableExists($variableID))) {
            return false;
        }

        return json_encode([
            'Ident' => $variableIdent,
            'Name' => $variableName,
            'Value' => $this->GetValue($variableIdent),
            'Suffix' => $variableProfileInfo["Suffix"],
            "Associations" => $variableProfileInfo["Associations"]
            // "CustomSuffix" => $variableCustomProfileInfo["Suffix"],
            // "CustomAssociations" => $variableCustomProfileInfo["Associations"]
        ]);
    }
}