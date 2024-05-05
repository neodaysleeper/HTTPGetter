<?php

require_once __DIR__ . '/../libs/IPSModule.php';

class HTTPGetter extends IPSModule {
    public function Create() {
        // Beim Erstellen der Instanz ausgeführt
        parent::Create();
        $this->RegisterPropertyString("WebhookName", "default_hook");
    }

    public function ApplyChanges() {
        // Beim Anwenden der Änderungen ausgeführt
        parent::ApplyChanges();

        // Webhook erstellen oder aktualisieren
        $hook = "/" . $this->ReadPropertyString("WebhookName");
        $this->RegisterHook($hook);
    }

    private function RegisterHook($WebHook) {
        $hookID = @IPS_GetObjectIDByIdent($WebHook, $this->InstanceID);
        if ($hookID === false) {
            $hookID = IPS_CreateInstance("{6179ED6A-FC31-413C-BB8E-1204150CF376}"); // WebHook Instanz
            IPS_SetParent($hookID, $this->InstanceID); // Unter diese Instanz verschieben
            IPS_SetIdent($hookID, $WebHook);
            IPS_SetName($hookID, "HTTP Getter Hook");
            IPS_SetProperty($hookID, "Hook", $WebHook);
            IPS_ApplyChanges($hookID);
        }
    }

    /**
     * Die Funktion, die aufgerufen wird, wenn der WebHook ausgelöst wird.
     */
    public function ReceiveData($JSONString) {
        $data = json_decode($JSONString, true);

        switch ($data['function']) {
            case "ZW_SwitchMode":
                ZW_SwitchMode($data['id'], $data['state']);
                echo "Z-wave device with ID {$data['id']} was switched to {$data['state']}";
                break;
            case "ZW_DimSet":
                ZW_DimSet($data['id'], intval($data['state']));
                echo "Z-wave device with ID {$data['id']} was dimmed to {$data['state']}";
                break;
            default:
                echo "The function '{$data['function']}' is not registered";
                break;
        }
    }
}
