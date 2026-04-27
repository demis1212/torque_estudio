<?php
namespace App\Controllers;

use App\Models\WorkshopOps;
use App\Models\WorkOrderPartRequest;
use App\Models\Part;

class WorkshopOpsController {
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') {
                $basePath = '';
            }
            redirect($basePath . '/login');
            exit;
        }
    }

    private function basePath() {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        return $basePath;
    }

    public function index() {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $orders = $ops->getOrdersSummary();
        $rates = $ops->getHourlyRates();

        view('workshop-ops/index', [
            'orders' => $orders,
            'rates' => $rates,
            'basePath' => $this->basePath(),
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name'] ?? 'Usuario',
        ]);
    }

    public function show($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();
        $partRequest = new WorkOrderPartRequest();
        $partModel = new Part();

        $order = $ops->getOrderDetails($id);
        if (!$order) {
            die('Orden no encontrada');
        }

        $activeEntry = $ops->getActiveTimeEntry($id);
        $entries = $ops->getTimeEntriesByOrder($id);
        $metrics = $ops->getMetricsByOrder($id);
        $rates = $ops->getHourlyRates();
        $quality = $ops->getQualityChecklist($id);
        $billingDocs = $ops->getBillingDocuments($id);
        $partRequests = $partRequest->getByWorkOrder($id);
        $parts = $partModel->all(); // Para seleccionar repuestos existentes

        view('workshop-ops/show', [
            'order' => $order,
            'activeEntry' => $activeEntry,
            'entries' => $entries,
            'metrics' => $metrics,
            'rates' => $rates,
            'quality' => $quality,
            'billingDocs' => $billingDocs,
            'partRequests' => $partRequests,
            'parts' => $parts,
            'basePath' => $this->basePath(),
            'userId' => $_SESSION['user_id'],
            'userName' => $_SESSION['user_name'] ?? 'Usuario',
            'userRole' => $_SESSION['user_role'] ?? 0,
        ]);
    }

    public function start($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $rateCode = $_POST['rate_code'] ?? 'mecanica_general';
        $notes = $_POST['notes'] ?? null;

        $activeEntry = $ops->getActiveTimeEntry($id, $_SESSION['user_id']);
        if (!$activeEntry) {
            $ops->startWork($id, $_SESSION['user_id'], $rateCode, $notes);
        }

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    public function pause($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $activeEntry = $ops->getActiveTimeEntry($id, $_SESSION['user_id']);
        if ($activeEntry && $activeEntry['status'] === 'running') {
            $reason = $_POST['reason'] ?? 'otro';
            $notes = $_POST['notes'] ?? null;
            $ops->pauseWork($activeEntry['id'], $reason, $notes);
        }

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    public function resume($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $activeEntry = $ops->getActiveTimeEntry($id, $_SESSION['user_id']);
        if ($activeEntry && $activeEntry['status'] === 'paused') {
            $ops->resumeWork($activeEntry['id']);
        }

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    public function finish($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $activeEntry = $ops->getActiveTimeEntry($id, $_SESSION['user_id']);
        if ($activeEntry) {
            $ops->finishWork($activeEntry['id']);
        }

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    public function saveQuality($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $data = $_POST;
        $data['signed_by_user_id'] = $_SESSION['user_id'];
        if (empty($data['signed_name'])) {
            $data['signed_name'] = $_SESSION['user_name'] ?? 'Firma';
        }

        $ops->saveQualityChecklist($id, $data);

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    public function createBilling($id) {
        $this->checkAuth();
        $ops = new WorkshopOps();

        $documentType = $_POST['document_type'] ?? 'boleta';
        $number = $_POST['document_number'] ?? '';

        if ($number !== '') {
            $ops->createBillingDocument($id, [
                'document_type' => $documentType,
                'document_number' => $number,
                'payment_method' => $_POST['payment_method'] ?? null,
                'payment_status' => $_POST['payment_status'] ?? 'pendiente',
            ], $_SESSION['user_id']);
        }

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    // === SOLICITUDES DE REPUESTOS (Gestión Inteligente) ===

    public function requestPart($id) {
        $this->checkAuth();
        $partRequest = new WorkOrderPartRequest();

        $partId = $_POST['part_id'] ?? null;
        $partName = $_POST['part_name'] ?? '';
        $quantity = $_POST['quantity'] ?? 1;
        $notes = $_POST['notes'] ?? '';

        if (empty($partName)) {
            $_SESSION['error'] = 'Debe especificar el nombre del repuesto';
            redirect($this->basePath() . '/workshop-ops/' . $id);
        }

        $success = $partRequest->create([
            'work_order_id' => $id,
            'part_id' => $partId,
            'part_name' => $partName,
            'quantity' => $quantity,
            'requested_by' => $_SESSION['user_id'],
            'notes' => $notes
        ]);

        if ($success) {
            $_SESSION['success'] = 'Solicitud de repuesto enviada correctamente';
        } else {
            $_SESSION['error'] = 'Error al crear la solicitud';
        }

        redirect($this->basePath() . '/workshop-ops/' . $id);
    }

    public function approvePartRequest($workOrderId, $requestId) {
        $this->checkAuth();
        
        // Solo admin (1) o bodeguero puede aprobar
        if ($_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'No tiene permisos para aprobar solicitudes';
            redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
        }

        $partRequest = new WorkOrderPartRequest();
        $request = $partRequest->find($requestId);

        if (!$request) {
            $_SESSION['error'] = 'Solicitud no encontrada';
            redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
        }

        $success = $partRequest->approve($requestId, $_SESSION['user_id'], $request['part_id']);

        if ($success) {
            $_SESSION['success'] = 'Repuesto aprobado y descontado del inventario';
        } else {
            $_SESSION['error'] = 'No hay suficiente stock en inventario';
        }

        redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
    }

    public function rejectPartRequest($workOrderId, $requestId) {
        $this->checkAuth();
        
        // Solo admin (1) o bodeguero puede rechazar
        if ($_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'No tiene permisos para rechazar solicitudes';
            redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
        }

        $partRequest = new WorkOrderPartRequest();
        $partRequest->reject($requestId, $_SESSION['user_id']);

        $_SESSION['success'] = 'Solicitud rechazada';
        redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
    }

    public function despacharPartRequest($workOrderId, $requestId) {
        $this->checkAuth();
        
        // Solo admin (1) o bodeguero puede despachar
        if ($_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'No tiene permisos para despachar';
            redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
        }

        $partRequest = new WorkOrderPartRequest();
        $partRequest->despachar($requestId);

        $_SESSION['success'] = 'Repuesto despachado';
        redirect($this->basePath() . '/workshop-ops/' . $workOrderId);
    }
}
