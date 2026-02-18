<?php

namespace App\Filament\Resources\InstrumentResource\Pages;

use App\Models\DeliveryNote;
use App\Models\Instrument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SendToControl extends Page
{
    public ?array $data = [];
    
    public array $selectedInstruments = [];

    protected static string $view = 'filament.resources.instrument-resource.pages.send-to-control';

    public function mount(): void
    {
        $this->selectedInstruments = request()->get('instruments', []);
        
        $this->form->fill([
            'recipient' => '',
            'instruments' => $this->selectedInstruments,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Izberi merila')
                    ->schema([
                        Forms\Components\CheckboxList::make('instruments')
                            ->label('Merila')
                            ->options(function () {
                                return Instrument::whereIn('id', $this->selectedInstruments)
                                    ->where('status', '!=', 'V_KONTROLI')
                                    ->where('archived', false)
                                    ->get()
                                    ->mapWithKeys(function ($instrument) {
                                        return [$instrument->id => $instrument->internal_id . ' - ' . $instrument->name];
                                    });
                            })
                            ->required()
                            ->columns(2),
                    ]),
                
                Forms\Components\Section::make('Podatki dobavnice')
                    ->schema([
                        Forms\Components\TextInput::make('recipient')
                            ->label('Prejemnik')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ime zunanje kontrole'),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        
        DB::transaction(function () use ($data) {
            // Ustvari dobavnico
            $deliveryNote = DeliveryNote::create([
                'recipient' => $data['recipient'],
                'sender_id' => Auth::id(),
                'status' => 'ODPRTA',
            ]);
            
            // Dodaj merila na dobavnico in spremeni status
            foreach ($data['instruments'] as $instrumentId) {
                $instrument = Instrument::find($instrumentId);
                
                if ($instrument && $instrument->status !== 'V_KONTROLI') {
                    // Dodaj na dobavnico
                    $deliveryNote->items()->create([
                        'instrument_id' => $instrument->id,
                    ]);
                    
                    // Spremeni status merila
                    $instrument->update([
                        'status' => 'V_KONTROLI',
                    ]);
                }
            }
            
            // Preusmeri na dobavnico
            $this->redirect(route('filament.admin.resources.delivery-notes.edit', $deliveryNote));
        });
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('create')
                ->label('Ustvari dobavnico')
                ->submit('create')
                ->color('success'),
        ];
    }
}
