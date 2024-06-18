<?php

namespace App\Http\Controllers;

use App\Contracts\ReservationRepository;
use App\Data\ReservationData;
use App\Enums\PaymentStatusEnum;
use App\Events\ReservationStatusUpdated;
use App\Http\Requests\CreateReservationRequest;
use App\Http\Requests\DeleteReservationRequest;
use App\Http\Requests\GetReservationRequest;
use App\Http\Requests\ListReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationCollection;
use App\Http\Resources\ReservationResource;

class ReservationController extends Controller
{
    const NOT_FOUND = 'Reservation not found';

    public function __construct(private ReservationRepository $reservationRepository)
    {
        $this->resourceItem       = ReservationResource::class;
        $this->resourceCollection = ReservationCollection::class;
    }

    public function index(ListReservationRequest $request)
    {
        $reservations = $this->reservationRepository->paginate();
        return new ReservationCollection($reservations);
    }

    public function store(CreateReservationRequest $request)
    {
        $reservationData = ReservationData::fromRequest($request);
        $reservation = $this->reservationRepository->store($reservationData->toDbArray());
        return $this->respondWithItem($reservation);
    }

    public function show(GetReservationRequest $request, $id)
    {
        $reservation = $this->reservationRepository->findOneById($id);
        if (!$reservation) {
            return  $this->respondWithCustomData(['message' => self::NOT_FOUND], 404);
        }
        return $this->respondWithItem($reservation);
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        $reservationData = ReservationData::fromRequest($request);
        $reservation = $this->reservationRepository->findOneById($id);
        if (!$reservation) {
            return  $this->respondWithCustomData(['message' => self::NOT_FOUND], 404);
        }

        $reservation = $this->reservationRepository->update($reservation, $reservationData->toDbArray());

        return $this->respondWithItem($reservation->refresh());
    }

    public function destroy(DeleteReservationRequest $request, $id)
    {
        $reservation = $this->reservationRepository->findOneById($id);
        if (!$reservation) {
            return  $this->respondWithCustomData(['message' => self::NOT_FOUND], 404);
        }
        $this->reservationRepository->deleteWhereIds([$id]);
        return $this->respondWithCustomData(['message' => 'Reservation deleted successfully']);
    }
}
