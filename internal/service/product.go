package service

import (
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
)

type ProductService struct {
	productRepo *repository.ProductRepository
}

func NewProductService(productRepo *repository.ProductRepository) *ProductService {
	return &ProductService{
		productRepo: productRepo,
	}
}

// List 获取商品列表
func (s *ProductService) List(req *model.ProductListRequest) (*model.ProductListResponse, error) {
	products, total, err := s.productRepo.List(req)
	if err != nil {
		return nil, err
	}

	return &model.ProductListResponse{
		Total:   total,
		Records: products,
	}, nil
}

// GetByID 获取商品详情
func (s *ProductService) GetByID(id int64) (*model.ProductDetailResponse, error) {
	product, err := s.productRepo.GetByID(id)
	if err != nil {
		return nil, err
	}

	specs, err := s.productRepo.GetSpecs(id)
	if err != nil {
		return nil, err
	}

	gradePrices, err := s.productRepo.GetGradePrices(id)
	if err != nil {
		return nil, err
	}

	category, err := s.productRepo.GetCategory(product.CategoryID)
	if err != nil {
		return nil, err
	}

	return &model.ProductDetailResponse{
		Product:     *product,
		Specs:       specs,
		GradePrices: gradePrices,
		Category:    *category,
	}, nil
}

// Create 创建商品
func (s *ProductService) Create(req *model.ProductCreateRequest) (*model.Product, error) {
	product := &model.Product{
		Name:            req.Name,
		Description:     req.Description,
		Price:           req.Price,
		Type:            int64(req.Type),
		ISP:             req.ISP,
		Status:          req.Status,
		Sort:            req.Sort,
		APIEEnabled:     req.APIEnabled,
		Remark:          req.Remark,
		CategoryID:      req.CategoryID,
		OperatorTag:     req.OperatorTag,
		MaxPrice:        req.MaxPrice,
		VoucherPrice:    req.VoucherPrice,
		VoucherName:     req.VoucherName,
		ShowStyle:       req.ShowStyle,
		APIFailStyle:    req.APIFailStyle,
		AllowProvinces:  req.AllowProvinces,
		AllowCities:     req.AllowCities,
		ForbidProvinces: req.ForbidProvinces,
		ForbidCities:    req.ForbidCities,
		APIDelay:        req.APIDelay,
		GradeIDs:        req.GradeIDs,
		APIID:           req.APIID,
		APIParamID:      req.APIParamID,
		IsApi:           req.IsApi,
	}

	err := s.productRepo.Create(product)
	if err != nil {
		return nil, err
	}

	return product, nil
}

// Update 更新商品
func (s *ProductService) Update(req *model.ProductUpdateRequest) (*model.Product, error) {
	product, err := s.productRepo.GetByID(req.ID)
	if err != nil {
		return nil, err
	}

	product.Name = req.Name
	product.Description = req.Description
	product.Price = req.Price
	product.Type = int64(req.Type)
	product.ISP = req.ISP
	product.Status = req.Status
	product.Sort = req.Sort
	product.APIEEnabled = req.APIEnabled
	product.Remark = req.Remark
	product.CategoryID = req.CategoryID
	product.OperatorTag = req.OperatorTag
	product.MaxPrice = req.MaxPrice
	product.VoucherPrice = req.VoucherPrice
	product.VoucherName = req.VoucherName
	product.ShowStyle = req.ShowStyle
	product.APIFailStyle = req.APIFailStyle
	product.AllowProvinces = req.AllowProvinces
	product.AllowCities = req.AllowCities
	product.ForbidProvinces = req.ForbidProvinces
	product.ForbidCities = req.ForbidCities
	product.APIDelay = req.APIDelay
	product.GradeIDs = req.GradeIDs
	product.APIID = req.APIID
	product.APIParamID = req.APIParamID
	product.IsApi = req.IsApi

	err = s.productRepo.Update(product)
	if err != nil {
		return nil, err
	}

	return product, nil
}

// Delete 删除商品
func (s *ProductService) Delete(id int64) error {
	return s.productRepo.Delete(id)
}

// ListCategories 获取商品分类列表
func (s *ProductService) ListCategories() (*model.ProductCategoryListResponse, error) {
	categories, err := s.productRepo.ListCategories()
	if err != nil {
		return nil, err
	}

	return &model.ProductCategoryListResponse{
		Total: int64(len(categories)),
		List:  categories,
	}, nil
}

// CreateCategory 创建商品分类
func (s *ProductService) CreateCategory(category *model.ProductCategory) error {
	return s.productRepo.CreateCategory(category)
}

// UpdateCategory 更新商品分类
func (s *ProductService) UpdateCategory(category *model.ProductCategory) error {
	return s.productRepo.UpdateCategory(category)
}

// DeleteCategory 删除商品分类
func (s *ProductService) DeleteCategory(id int64) error {
	return s.productRepo.DeleteCategory(id)
}

// ListTypes 获取商品类型列表
func (s *ProductService) ListTypes() ([]model.ProductType, error) {
	return s.productRepo.ListTypes()
}
