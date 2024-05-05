<?php

class HTTPGetter extends IPSModule
{
    public function Create()
    {
        // Diese Funktion wird beim Erstellen der Instanz ausgeführt.
        parent::Create();
        $this->RegisterPropertyString("WebhookName", "httpgetter"); // Standardname
    }

    public function ApplyChanges()
    {
        // Diese Funktion wird beim Anwenden der Änderungen aufgerufen.
        parent::ApplyChanges();
        $this->RegisterWebhook($this->ReadPropertyString("WebhookName"));
    }

    private function RegisterWebhook($WebhookName)
    {
        $hook = "/hook/" . $WebhookName;
        $id = @IPS_GetObjectIDByIdent($hook, 0);

        if ($id === false) {
            $id = IPS_CreateInstance("{6179ED6A-FC31-413C-BB8E-1204150CF376}"); // WebHook Instanz
            IPS_SetIdent($id, $hook);
            IPS_SetName($id, "Webhook " . $WebhookName);
            IPS_SetParent($id, 0);
            IPS_ApplyChanges($id);
        }

        IPS_SetProperty($id, "Hook", $hook);
        IPS_SetProperty($id, "TargetID", $this->InstanceID);
        IPS_ApplyChanges($id);
    }

    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString, true);
        IPS_LogMessage("HTTPGetter", print_r($data, true));

        if ($_IPS['SENDER'] == "WebHook") {
            $user = htmlspecialchars($_GET['user']);
            $password = htmlspecialchars($_GET['password']);
            $id = intval($_GET['id']);
            $function = htmlspecialchars($_GET['function']);
            $state = htmlspecialchars($_GET['state']);

            switch ($function) {
                case "ZW_SwitchMode":
                    ZW_SwitchMode($id, $state);
                    echo "Z-Wave Gerät mit ID {$id} wurde auf {$state} geschaltet";
                    break;
                case "ZW_DimSet":
                    ZW_DimSet($id, intval($state));
                    echo "Z-Wave Gerät mit ID {$id} wurde gedimmt auf {$state}";
                    break;
                default:
                    echo "Die Funktion '{$function}' ist nicht hinterlegt.";
                    break;
            }
        }
    }
}
