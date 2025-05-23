<?php

namespace Tourze\HotelProfileBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\HotelProfileBundle\Enum\HotelStatusEnum;

/**
 * 酒店数据导入导出服务
 */
class HotelImportExportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 导出酒店数据到Excel
     *
     * @return array 临时文件路径和文件名
     */
    public function exportHotelsToExcel(): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置表头
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', '酒店名称');
        $sheet->setCellValue('C1', '详细地址');
        $sheet->setCellValue('D1', '星级');
        $sheet->setCellValue('E1', '联系人');
        $sheet->setCellValue('F1', '联系电话');
        $sheet->setCellValue('G1', '联系邮箱');
        $sheet->setCellValue('H1', '状态');
        
        // 样式
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        
        // 添加数据
        $hotels = $this->entityManager->getRepository(Hotel::class)->findAll();
        $row = 2;
        
        foreach ($hotels as $hotel) {
            $sheet->setCellValue('A' . $row, $hotel->getId());
            $sheet->setCellValue('B' . $row, $hotel->getName());
            $sheet->setCellValue('C' . $row, $hotel->getAddress());
            $sheet->setCellValue('D' . $row, $hotel->getStarLevel());
            $sheet->setCellValue('E' . $row, $hotel->getContactPerson());
            $sheet->setCellValue('F' . $row, $hotel->getPhone());
            $sheet->setCellValue('G' . $row, $hotel->getEmail());
            $sheet->setCellValue('H' . $row, $hotel->getStatus()->getLabel());
            
            $row++;
        }
        
        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(15);
        
        // 创建写入器并设置响应
        $writer = new Xlsx($spreadsheet);
        $fileName = '酒店列表_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'hotel_export_');
        $writer->save($tempFile);
        
        return [
            'file_path' => $tempFile,
            'file_name' => $fileName,
            'disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT
        ];
    }
    
    /**
     * 创建Excel导入模板
     *
     * @return array 临时文件路径和文件名
     */
    public function createImportTemplate(): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置表头
        $sheet->setCellValue('A1', 'ID (导入时不需填写)');
        $sheet->setCellValue('B1', '酒店名称 (必填)');
        $sheet->setCellValue('C1', '详细地址 (必填)');
        $sheet->setCellValue('D1', '星级 (1-5) (必填)');
        $sheet->setCellValue('E1', '联系人 (必填)');
        $sheet->setCellValue('F1', '联系电话 (必填)');
        $sheet->setCellValue('G1', '联系邮箱');
        $sheet->setCellValue('H1', '状态 (导入时默认为"运营中")');
        
        // 样式
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        
        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        
        // 创建写入器并设置响应
        $writer = new Xlsx($spreadsheet);
        $fileName = '酒店导入模板.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'hotel_template_');
        $writer->save($tempFile);
        
        return [
            'file_path' => $tempFile,
            'file_name' => $fileName,
            'disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT
        ];
    }
    
    /**
     * 从Excel导入酒店数据
     *
     * @param UploadedFile $excelFile
     * @return array 导入结果，包含导入数量和错误信息
     */
    public function importHotelsFromExcel(UploadedFile $excelFile): array
    {
        $result = [
            'success' => true,
            'import_count' => 0,
            'errors' => []
        ];
        
        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($excelFile->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            $importCount = 0;
            $errors = [];
            
            // 从第2行开始，第1行是表头
            for ($row = 2; $row <= $highestRow; $row++) {
                $hotelName = $worksheet->getCell('B'.$row)->getValue();
                $address = $worksheet->getCell('C'.$row)->getValue();
                $starLevel = (int)$worksheet->getCell('D'.$row)->getValue();
                $contactPerson = $worksheet->getCell('E'.$row)->getValue();
                $phone = $worksheet->getCell('F'.$row)->getValue();
                $email = $worksheet->getCell('G'.$row)->getValue();
                
                // 数据验证
                if (empty($hotelName) || empty($address) || empty($contactPerson) || empty($phone)) {
                    $errors[] = "第 {$row} 行数据不完整";
                    continue;
                }
                
                if ($starLevel < 1 || $starLevel > 5) {
                    $errors[] = "第 {$row} 行星级数据无效";
                    continue;
                }
                
                try {
                    // 检查是否已存在同名酒店
                    $existingHotel = $this->entityManager->getRepository(Hotel::class)
                        ->findOneBy(['name' => $hotelName]);
                    
                    if ($existingHotel) {
                        // 更新现有酒店
                        $existingHotel->setAddress($address);
                        $existingHotel->setStarLevel($starLevel);
                        $existingHotel->setContactPerson($contactPerson);
                        $existingHotel->setPhone($phone);
                        $existingHotel->setEmail($email);
                        
                        $this->logger->info('更新酒店信息', [
                            'id' => $existingHotel->getId(),
                            'name' => $hotelName
                        ]);
                    } else {
                        // 创建新酒店
                        $hotel = new Hotel();
                        $hotel->setName($hotelName);
                        $hotel->setAddress($address);
                        $hotel->setStarLevel($starLevel);
                        $hotel->setContactPerson($contactPerson);
                        $hotel->setPhone($phone);
                        $hotel->setEmail($email);
                        $hotel->setStatus(HotelStatusEnum::OPERATING);
                        
                        $this->entityManager->persist($hotel);
                        
                        $this->logger->info('创建新酒店', [
                            'name' => $hotelName
                        ]);
                    }
                    
                    $importCount++;
                } catch (\Exception $e) {
                    $errors[] = "第 {$row} 行处理失败: " . $e->getMessage();
                    $this->logger->error('导入酒店数据失败', [
                        'row' => $row, 
                        'name' => $hotelName, 
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $this->entityManager->flush();
            
            $result['import_count'] = $importCount;
            $result['errors'] = $errors;
            
            return $result;
            
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = '导入过程发生错误: ' . $e->getMessage();
            $this->logger->error('Excel导入失败', ['error' => $e->getMessage()]);
            
            return $result;
        }
    }
}
