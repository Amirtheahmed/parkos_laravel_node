<?php

namespace App\Http\Controllers;

use App\Contracts\ReservationRepository;
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
        $reservation = $this->reservationRepository->store($request->validated());
        return new ReservationResource($reservation);
    }

    public function show(GetReservationRequest $request, $id)
    {
        $reservation = $this->reservationRepository->findOneById($id);
        if (!$reservation) {
            return response()->json(['message' => self::NOT_FOUND], 404);
        }
        return new ReservationResource($reservation);
    }

    public function update(UpdateReservationRequest $request, $id)
    {
        $reservation = $this->reservationRepository->findOneById($id);
        if (!$reservation) {
            return response()->json(['message' => self::NOT_FOUND], 404);
        }
        $reservation = $this->reservationRepository->update($reservation, $request->validated());
        return new ReservationResource($reservation);
    }

    public function destroy(DeleteReservationRequest $request, $id)
    {
        $reservation = $this->reservationRepository->findOneById($id);
        if (!$reservation) {
            return response()->json(['message' => self::NOT_FOUND], 404);
        }
        $this->reservationRepository->deleteWhereIds([$id]);
        return response()->json(['message' => 'Reservation deleted successfully'], 200);
    }
}
