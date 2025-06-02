<?php

namespace Modules\Pos\Services;

use App\Http\Services\CustomPaginationService;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaravelIdea\Helper\App\Models\_IH_User_C;

class CustomerServices
{
    private string $customer_query;
    protected ?CustomerServices $instance = null;
    protected bool $enableSelect = false;
    protected bool $enableWith = false;
    protected string $conditionType = 'where';
    protected array $selectedColumn = [];
    protected array $withRelation = [];

    private function setConditionType(string $type = "where"): static
    {
        $this->conditionType = $type;

        return $this;
    }

    private function setEnableSelect($bool): static
    {
        $this->enableSelect = $bool;

        return $this;
    }


    private function setEnableWith($bool): static
    {
        $this->enableWith = $bool;

        return $this;
    }

    private function select(...$arg): static
    {
        $this->selectedColumn = $arg;

        return $this;
    }

    private function with(...$arg): static
    {
        $this->setEnableWith(true);
        $this->withRelation = $arg;

        return $this;
    }

    private function query(): Builder
    {
        return User::query();
    }

    private static function instance(): CustomerServices
    {
        $self = new self();
        if (!is_null($self->instance)){
            return $self->instance;
        }

        return $self;
    }

    private function queryCondition(): Builder
    {
        $query = $this->query();

        if($this->enableSelect){
            $query->select($this->selectedColumn);
        }
        if($this->enableWith){
            $query->with($this->withRelation);
        }

        return $query->where([$this->conditionType()]);
    }

    public function setCustomerQuery($query): CustomerServices
    {
        $inst = $this;
        $inst->customer_query = $query;

        return $inst;
    }

    public function searchType(): string
    {
        return filter_var($this->customer_query, FILTER_VALIDATE_EMAIL) ? "email" : "mobile";
    }

    private function conditionType(): array
    {
        $column = $this->searchType();

        return match ($this->conditionType){
            default => [$column, "=", $this->customer_query],
            "like" => [$column, "LIKE", $this->customer_query],
            "like_from_start" => [$column, "LIKE", $this->customer_query . "%"],
            "like_from_end" => [$column, "LIKE","%" . $this->customer_query],
            "like_both" => [$column, "LIKE","%" . $this->customer_query . "%"],
            "not_equal" => [$column, "!=" , $this->customer_query],
        };
    }

    private function get(): Collection|array|_IH_User_C
    {
        $query = $this->queryCondition();
        return $query->get();
    }

    public function paginate($limit = 5, $isCustomPagination = false): LengthAwarePaginator|_IH_User_C|\Illuminate\Pagination\LengthAwarePaginator|array
    {
        $query = $this->queryCondition();

        if($isCustomPagination){
            return CustomPaginationService::pagination_type($query, $limit);
        }

        return $query->paginate();
    }

    public static function searchCustomer($customer_query): Collection|_IH_User_C|array
    {
        return self::instance()
            ->setEnableSelect(true)
            ->setConditionType("like_from_start")
            ->select("id","mobile","email","name",'username',"image")
            ->setCustomerQuery($customer_query)
            ->get();
    }

    public static function findCustomer($id): Model|Collection|Builder|_IH_User_C|User|array|_IH_User_QB|null
    {
        return self::instance()
            ->select("id","mobile","email","name","image","created_at")
            ->find($id);
    }

    private function find($id): Model|Collection|array|Builder|_IH_User_C|User|_IH_User_QB|null
    {
        return $this->query()
            ->select($this->selectedColumn)
            ->with($this->withRelation)
            ->find($id);
    }
}
