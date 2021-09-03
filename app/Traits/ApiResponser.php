<?php
    namespace App\Traits;

    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Collection;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Cache;

    trait ApiResponser {
        private function successResponse($data, $code) {
            return response()->json($data, $code);
        }
        protected function errorResponse($message, $code) {
            return response()->json(['error'=>$message, 'code'=>$code], $code);
        }
        protected function showAll(Collection $collection, $code = 200) {
            if ($collection->isEmpty()) {
                return $this->successResponse(['data' => $collection], $code);
            }
            $transformer = $collection->first()->transformer;

            //This must be executed before the transformData() method, but then it is sorting by original attributes
            //and not transformed attributes.  Need a method to map the original attributes to the transformed names,
            //we will do this in our transformers - originalAttribute().

            //Filter the data before the sort - that way you sort less and things go faster
            $collection = $this->filterData($collection, $transformer);
            //Sort
            $collection = $this->sortData($collection, $transformer);
            //Now paginate
            $collection = $this->paginate($collection);
            //And Transform
            $collection = $this->transformData($collection, $transformer);
            //Cache Response
            $collection = $this->cacheResponse($collection, $transformer);

            return $this->successResponse($collection, $code);
        }
        protected function showOne(Model $instance, $code = 200) {
            $transformer = $instance->transformer;

            $instance = $this->transformData($instance, $transformer);
            return $this->successResponse($instance, $code);
        }
        protected function showMessage($message, $code = 200) {
            return $this->successResponse(['data' => $message], $code);
        }

        protected function filterData(Collection $collection,$transformer) {
            //loop through query parameters
            foreach (request()->query() as $query => $value) {
                $attribute = $transformer::originalAttribute($query);
                if (isset($attribute, $value)) {
                    $collection = $collection->where($attribute, $value);
                }
            }
            return $collection;
        }

        //sorting by original names of data and not transformed names of data
        protected function sortData(Collection $collection, $transformer) {
            if (request()->has('sort_by')) {
                $attribute = $transformer::originalAttribute(request()->sort_by);
                $collection = $collection->sortBy($attribute);
            }
            return $collection;
        }

        //pagination
        protected function paginate(Collection $collection) {
            request()->validate([
                'per_page' => 'integer|min:2|max:50',
            ]);
            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 15;
            if (request()->has('per_page')) {
                $perPage = (int)request()->per_page;
            }
            $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
            $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);
            $paginated->appends(request()->all());
            return $paginated;
        }

        protected function transformData($data, $transformer) {
            $transformation = fractal($data, new $transformer);
            return $transformation->toArray();
        }

        //30 seconds - Using cache like this will ruin the search results, pagination or sort because the
        //cached response will override all of our url parameters
        protected function cacheResponse($data) {
            $url = request()->url();
            $queryParams = request()->query();
            ksort($queryParams);
            $queryString = http_build_query($queryParams);
            $fullUrl = "{$url}?{$queryString}";
            return Cache::remember($fullUrl, 30, function() use($data) {
                return $data;
            });
        }
    }
